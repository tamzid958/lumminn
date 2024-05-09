<?php


namespace App\Contracts\Shipping;


interface ShippingGatewayFactory
{
    public function create(): ShippingGateway;
}
