<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;
use App\Utils\StringUtil;

class PickUpGateway extends BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {
        $order['shipping_id'] = StringUtil::generateReadableString();

        parent::send($order);
    }
}
