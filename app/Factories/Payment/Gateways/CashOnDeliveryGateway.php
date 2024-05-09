<?php

namespace App\Factories\Payment\Gateways;


use App\Contracts\Payment\PaymentGateway;
use App\Utils\StringUtil;

class CashOnDeliveryGateway implements PaymentGateway
{
    public function generateTransaction(array $order): string
    {
        return StringUtil::generateReadableString();
    }
}
