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

class SendOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $orderIds;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $orderIds, $user)
    {
        $this->orderIds = $orderIds;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            foreach ($this->orderIds as $orderId) {
                $dbOrder = Order::query()->find($orderId)->toArray();
                $shipping_provider = ShippingProvider::query()->find($dbOrder['shipping_provider_id']);

                ShippingServiceProvider::register($shipping_provider)->create()->send($dbOrder);
            }

            Notification::make()
                ->title('Sent to shipping provider')
                ->icon('heroicon-o-paper-airplane')
                ->body("Orders successfully sent to shipping provider")
                ->sendToDatabase($this->user);
        } catch (Exception $e) {
            Notification::make()
                ->title('Sent to shipping provider failed')
                ->icon('heroicon-o-document-text')
                ->body("Check failed jobs table to see error logs")
                ->sendToDatabase($this->user);
        }

        dispatch(new GenerateInvoiceJob($this->orderIds, $this->user))->delay(3);
        //
    }
}
