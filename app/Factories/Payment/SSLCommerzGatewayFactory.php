<?php

namespace App\Factories\Payment;

use App\Contracts\Payment\PaymentGatewayFactory;
use App\Contracts\Payment\PaymentGateway;
use App\Factories\Payment\Gateways\SSLCommerzGateway;

class SSLCommerzGatewayFactory implements PaymentGatewayFactory
{
    public function create(): PaymentGateway
    {
        return new SSLCommerzGateway();
    }
}
