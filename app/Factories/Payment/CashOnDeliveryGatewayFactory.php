<?php

namespace App\Factories\Payment;

use App\Contracts\Payment\PaymentGateway;
use App\Contracts\Payment\PaymentGatewayFactory;
use App\Factories\Payment\Gateways\CashOnDeliveryGateway;

class CashOnDeliveryGatewayFactory implements PaymentGatewayFactory
{
    public function create(): PaymentGateway
    {
        return new CashOnDeliveryGateway();
    }
}
