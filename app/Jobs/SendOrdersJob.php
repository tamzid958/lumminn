<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ShippingProvider;
use App\Providers\ShippingServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\PaymentProvider;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

class SendOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $orders;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $orders, $user)
    {
        $this->orders = $orders;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->orders as $order) {
            $dbOrder = Order::query()->find($order['id'])->toArray();
            $shipping_provider = ShippingProvider::query()->find($order['shipping_provider_id']);

            ShippingServiceProvider::register($shipping_provider)->create()->send($dbOrder);
        }

        Notification::make()
            ->title('Sent to shipping provider')
            ->icon('heroicon-o-paper-airplane')
            ->body("Orders successfully sent to shipping provider")
            ->sendToDatabase($this->user);
            
        dispatch(new GenerateInvoiceJob($this->orders, $this->user))->delay(3);
        //
    }
}
