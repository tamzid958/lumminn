<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;
use App\Models\Order;

abstract class BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {
        Order::query()->where('id', $order['id'])->update($order);
    }
}
