<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;
use App\Models\Order;
use App\Models\PaymentProvider;

abstract class BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {
        $order['shipping_status'] = 'Packed';
        Order::query()->where('id', $order['id'])->update($order);
    }

    public function check(array $order): void
    {
        $payment_provider = PaymentProvider::query()->find($order['payment_provider_id']);
        $payment_provider_slug = $payment_provider->slug;

        if ($payment_provider_slug === 'cash-on-delivery') {
            $order['pay_status'] = match ($order['shipping_status']) {
                "Completed" => 'Paid',
                "Cancelled" => 'Cancelled',
                default => $order['pay_status']
            };
        }

        Order::query()->where('id', $order['id'])->update($order);
    }
}
