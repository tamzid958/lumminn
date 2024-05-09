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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $orders;
    /**
     * Create a new job instance.
     */
    public function __construct(array $orders)
    {
        $this->orders = $orders;
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
        //
    }
}
