<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;
use App\Models\Order;
use App\Utils\StringUtil;

class PickUpGateway extends BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {
        $order['shipping_id'] = StringUtil::generateReadableString();
        $order['shipping_status'] = 'Dispatched';
        parent::send($order);
    }
}
