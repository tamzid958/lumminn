<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\ShippingProvider;
use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use Illuminate\Support\Facades\DB;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Collection $orders;
    protected $user;

    public $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $orders, $user)
    {
        $this->orders = $orders;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $orders = $this->orders->toArray();

        try {
            $packingReceipts = collect($orders)->map(function ($record) {
                $orderItems = DB::table('order_items')
                                ->leftJoin('products as product', 'order_items.product_id', '=', 'product.id')
                                ->leftJoin('optional_products as optional_product', 'order_items.optional_product_id', '=', 'optional_product.id')
                                ->select('order_items.*', 'product.name as product_name', 'optional_product.name as optional_product_name')
                                ->where('order_items.order_id', $record['id'])
                                ->get();
                                
                $productsString = '';

                foreach ($orderItems as $item) {
                    $productName = $item->product_id !== null ? $item->product_name : $item->optional_product_name;
                    $quantity = $item->quantity;
                    $itemString = "$productName ($quantity)";

                    $productsString .= ($productsString ? ', ' : '') . $itemString;
                }

                return [
                    'id' => $record['id'],
                    'name' => $record['name'],
                    'phone_number' => $record['phone_number'],
                    'address' => $record['address'],
                    'shipping_id' => $record['shipping_id'],
                    'shipping_provider_name' => ShippingProvider::query()->find($record['shipping_provider_id'])->name,
                    'due_amount' => PaymentProvider::query()->find($record['payment_provider_id'])->slug === 'cash-on-delivery' ? $record['pay_amount'] : 0,
                    'order_items' => $productsString
                ];
            });

            $filename = time() . "-invoice.pdf";

            LaravelMpdf::loadView('components.download-invoice',
                ['packingReceipts' => $packingReceipts])
                ->save(public_path('storage') . '/' . $filename);

            Invoice::query()->create([
                "name" => $filename,
                "file" => $filename
            ]);

            Notification::make()
                ->title('Invoice generated')
                ->icon('heroicon-o-document-text')
                ->body("Download " . $filename . " and print it for packaging")
                ->actions([
                    Action::make('Download')
                        ->button()
                        ->url(asset('storage/' . $filename), shouldOpenInNewTab: true)
                ])
                ->sendToDatabase($this->user);
        } catch (Exception $e) {
            Notification::make()
                ->title('Invoice generation failed')
                ->icon('heroicon-o-document-text')
                ->body("Check failed jobs table to see error logs")
                ->sendToDatabase($this->user);
        }
    }
}
