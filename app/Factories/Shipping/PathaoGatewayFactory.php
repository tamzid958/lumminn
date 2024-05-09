<?php

namespace App\Factories\Shipping;

use App\Contracts\Shipping\ShippingGateway;
use App\Contracts\Shipping\ShippingGatewayFactory;
use App\Factories\Shipping\Gateways\PathaoGateway;

class PathaoGatewayFactory implements ShippingGatewayFactory
{
    public function create(): ShippingGateway
    {
        return new PathaoGateway();
    }
}
