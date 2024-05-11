<?php

namespace App\Contracts\Payment;


interface PaymentGateway
{
    public function generateTransaction(array $order): void;

    public function verify(string $invoice_id): void;
}
