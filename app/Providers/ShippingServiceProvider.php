<?php

namespace App\Providers;

use App\Factories\Shipping\PathaoGatewayFactory;
use App\Factories\Shipping\PickUpGatewayFactory;
use App\Factories\Shipping\SteadFastGatewayFactory;

class ShippingServiceProvider
{
    public static function register($provider): SteadFastGatewayFactory|PathaoGatewayFactory|PickUpGatewayFactory
    {
        return match ($provider->slug) {
            'pathao' => new PathaoGatewayFactory(),
            'steadfast' => new SteadFastGatewayFactory(),
            default => new PickUpGatewayFactory(),
        };
    }
}
