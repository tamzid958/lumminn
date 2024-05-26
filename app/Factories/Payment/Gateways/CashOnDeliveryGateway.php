<?php

namespace App\Factories\Payment\Gateways;


use App\Contracts\Payment\PaymentGateway;
use App\Utils\StringUtil;

class CashOnDeliveryGateway extends BasePaymentGateway implements PaymentGateway
{
    public function generateTransaction(array $order): void
    {
        $order['payment_id'] = StringUtil::generateReadableString();
        parent::generateTransaction($order);
    }

    public function verify(string $invoice_id, array $order): void
    {
    }
}
