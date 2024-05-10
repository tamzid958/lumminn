<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;
use App\Models\Order;

abstract class BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {
        $order['shipping_status'] = 'Ready for Dispatch';
        Order::query()->where('id', $order['id'])->update($order);
    }
}
