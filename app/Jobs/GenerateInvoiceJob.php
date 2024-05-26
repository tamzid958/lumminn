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
use Illuminate\Support\Facades\Log;

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
        $packingReceipts = [];

        foreach ($this->orders as $order) {
            $orderItems = DB::table('order_items')
                        ->leftJoin('products as product', 'order_items.product_id', '=', 'product.id')
                        ->leftJoin('optional_products as optional_product', 'order_items.optional_product_id', '=', 'optional_product.id')
                        ->select('order_items.*', 'product.name as product_name', 'optional_product.name as optional_product_name')
                        ->where('order_items.order_id', $order->id)
                        ->get();

            $productsString = '';

            foreach ($orderItems as $item) {
                $productName = $item->product_id !== null ? $item->product_name : $item->optional_product_name;
                $quantity = $item->quantity;
                $itemString = "$productName ($quantity)";

                $productsString .= ($productsString ? ', ' : '') . $itemString;
            }

            $packingReceipts[] = [
                'id' => $order->id,
                'name' => $order->name,
                'phone_number' => $order->phone_number,
                'address' => $order->address,
                'shipping_id' => $order->shipping_id,
                'shipping_provider_name' => ShippingProvider::query()->find( $order->shipping_provider_id)->name,
                'due_amount' => $order->pay_amount,
                'order_items' => $productsString
            ];

        
            try {
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
                // Handle the exception (log it, notify someone, etc.)
                Log::error('Failed to generate order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                Notification::make()
                        ->title('Invoice generation failed')
                        ->icon('heroicon-o-document-text')
                        ->body("Check failed jobs table to see error logs")
                        ->sendToDatabase($this->user);
            }
            
        }     

    }
}
