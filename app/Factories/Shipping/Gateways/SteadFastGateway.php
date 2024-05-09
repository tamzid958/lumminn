<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;

class SteadFastGateway extends BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {

    }
}
