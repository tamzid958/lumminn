<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;
use App\Utils\StringUtil;

class PickUpGateway implements ShippingGateway
{
    public function send(array $order): string
    {
        return StringUtil::generateReadableString();
    }
}
