<?php

namespace App\Contracts\Payment;


interface PaymentGateway
{
    public function generateTransaction(array $order): void;

    public function verify(string $invoice_id, array $order): void;

    public function setPaymentId(string $invoice_id, string $payment_id): void;
}
