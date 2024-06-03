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

    public function verify(string $invoice_id, array $order): void
    {
        Order::query()->where('invoice_id', $invoice_id)->update($order);
    }

    public function setPaymentId(string $invoice_id, string $payment_id): void
    {
        $order = Order::query()->where('invoice_id', $invoice_id)->first();
        $order->payment_id = $payment_id;
        Order::query()->where('invoice_id', $invoice_id)->update(($order->toArray()));
    }
}
