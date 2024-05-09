<?php


namespace App\Contracts\Payment;


interface PaymentGatewayFactory
{
    public function create(): PaymentGateway;
}
