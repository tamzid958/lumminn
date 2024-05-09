<?php

namespace App\Factories\Payment\Gateways;


use App\Contracts\Payment\PaymentGateway;

class SSLCommerzGateway implements PaymentGateway
{
    public function generateTransaction(array $order)
    {

    }
}
