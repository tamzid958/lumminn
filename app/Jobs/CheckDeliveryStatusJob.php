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

class CheckDeliveryStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 0;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $orders = Order::query()
            ->where('shipping_status', '=', 'Packed')
            ->orWhere('shipping_status', '=', 'Dispatched')
            ->get()
            ->toArray();

        foreach ($orders as $order) {
            $shipping_provider = ShippingProvider::query()->find($order['shipping_provider_id']);
            ShippingServiceProvider::register($shipping_provider)->create()->check($order);
        }
        //
    }
}
