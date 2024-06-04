<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ShippingProvider;
use App\Providers\ShippingServiceProvider;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    public $timeout = 0;
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $dbOrders = Order::query()
            ->where('shipping_status', 'On Hold')
            ->where('is_confirmed', true)
            ->get();

        if (count($dbOrders) === 0) return;

        foreach ($dbOrders as $order) {
            $shipping_provider = ShippingProvider::find($order->shipping_provider_id);

            if ($shipping_provider) {
                try {
                    ShippingServiceProvider::register($shipping_provider)
                        ->create()
                        ->send($order->toArray());
                } catch (Exception $e) {
                    // Handle the exception (log it, notify someone, etc.)
                    Log::error('Failed to send order', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                // Handle the case where the shipping provider is not found
                Log::warning('Shipping provider not found', ['shipping_provider_id' => $order->shipping_provider_id]);
            }
        }

        Notification::make()
            ->title('Sent to shipping provider')
            ->icon('heroicon-o-paper-airplane')
            ->body("Orders successfully sent to shipping provider")
            ->sendToDatabase($this->user);

        dispatch(new GenerateInvoiceJob($dbOrders, $this->user))->delay(5);
        //
    }
}
