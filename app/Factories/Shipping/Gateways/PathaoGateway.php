<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;

class PathaoGateway extends BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {
    }
    public function check(array $order): void
    {
    }
}
