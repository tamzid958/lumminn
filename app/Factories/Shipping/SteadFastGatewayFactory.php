<?php

namespace App\Factories\Shipping;

use App\Contracts\Shipping\ShippingGateway;
use App\Contracts\Shipping\ShippingGatewayFactory;
use App\Factories\Shipping\Gateways\SteadFastGateway;

class SteadFastGatewayFactory implements ShippingGatewayFactory
{
    public function create(): ShippingGateway
    {
        return new SteadFastGateway();
    }
}
