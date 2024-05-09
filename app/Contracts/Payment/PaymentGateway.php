<?php

namespace App\Contracts\Payment;


interface PaymentGateway
{
    public function generateTransaction(array $order);
}
