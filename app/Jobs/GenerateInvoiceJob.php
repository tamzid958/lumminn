<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\OrderItem;
use App\Utils\StringUtil;
use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    public $tries = 5;
    
    protected Collection $orders;
    protected $user;

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
        if (count($this->orders) === 0) return;

        $packingReceipts = [];

        foreach ($this->orders as $order) {
            $orderItems = OrderItem::query()
                ->where('order_id', '=', $order->id)
                ->with(['product', 'optionalProduct'])
                ->get();

            $productsString = '';

            foreach ($orderItems as $item) {
                $productName = $item->product ? $item->product->name : ($item->optionalProduct ? $item->optionalProduct->title : '');
                $quantity = $item->quantity;
                $itemString = "$productName ($quantity)";

                $productsString .= ($productsString ? ', ' : '') . $itemString;
            }

            $packingReceipts[] = [
                'id' => $order->id,
                'name' => StringUtil::unicodeToBijoy($order->name),
                'phone_number' => $order->phone_number,
                'address' => StringUtil::unicodeToBijoy($order->address),
                'shipping_id' => $order->shipping_id,
                'shipping_provider_name' => $order->shippingProvider ? $order->shippingProvider->name : '',
                'due_amount' => $order->pay_amount,
                'order_items' => $productsString
            ];
        }

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
        }

    }
}
