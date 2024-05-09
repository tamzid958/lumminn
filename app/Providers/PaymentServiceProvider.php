<?php

namespace App\Providers;

use App\Factories\Payment\CashOnDeliveryGatewayFactory;
use App\Factories\Payment\SSLCommerzGatewayFactory;

class PaymentServiceProvider
{
    public static function register($provider): SSLCommerzGatewayFactory|CashOnDeliveryGatewayFactory
    {
        return match ($provider->slug) {
            'sslcommerz' => new SSLCommerzGatewayFactory(),
            default => new CashOnDeliveryGatewayFactory(),
        };
    }
}
