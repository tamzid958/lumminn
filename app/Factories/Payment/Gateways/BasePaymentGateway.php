<?php

namespace App\Factories\Payment\Gateways;


use App\Contracts\Payment\PaymentGateway;
use App\Models\Order;

abstract class BasePaymentGateway implements PaymentGateway
{
    public function generateTransaction(array $order): void
    {
        Order::query()->where('id', $order['id'])->update($order);
    }

    public function verify(string $invoice_id): void
    {
    }
}
