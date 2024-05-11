<?php

namespace App\Contracts\Shipping;

interface ShippingGateway
{
    public function send(array $order);

    public function check(array $order);
}
