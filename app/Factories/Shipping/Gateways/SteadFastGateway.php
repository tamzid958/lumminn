<?php

namespace App\Factories\Shipping\Gateways;

use App\Contracts\Shipping\ShippingGateway;
use App\Models\ShippingProvider;
use App\Utils\StringUtil;
use Illuminate\Support\Facades\Http;
use Throwable;


class SteadFastGateway extends BaseShippingGateway implements ShippingGateway
{
    public function send(array $order): void
    {
        try {
            $shipping_provider = ShippingProvider::query()->find($order['shipping_provider_id']);

            $meta = $shipping_provider->meta;

            $baseUrl = $meta['baseUrl'];
            $apiKey = $meta['apiKey'];
            $secret = $meta['secret'];

            $response = Http::retry(3, 100)->withHeaders([
                'Api-Key' => $apiKey,
                'Secret-Key' => $secret,
                'Content-Type' => 'application/json'
            ])->post($baseUrl . '/create_order', [
                'invoice' => $order['invoice_id'],
                'recipient_name' => $order['name'],
                'recipient_phone' => StringUtil::removeCountryCode($order['phone_number']),
                'recipient_address' => $order['address'],
                'cod_amount' => $order['pay_amount'],
            ]);

            $body = $response->json();

            if ($body['status'] === 200) {
                $order['shipping_id'] = (string)$body['consignment']['consignment_id'];
                parent::send($order);
            } else {
                dump("response body error (". $order['id'] . ") " . $body);
            }
        } catch (Throwable $e) {
            dump("throwable error (". $order['id'] . ") ". $e->getMessage());
        }
    }

    public function check(array $order): void
    {
        try {
            $shipping_provider = ShippingProvider::query()->find($order['shipping_provider_id']);

            $meta = $shipping_provider->meta;

            $baseUrl = $meta['baseUrl'];
            $apiKey = $meta['apiKey'];
            $secret = $meta['secret'];

            $response = Http::retry(3, 100)->withHeaders([
                'Api-Key' => $apiKey,
                'Secret-Key' => $secret,
                'Content-Type' => 'application/json'
            ])->get($baseUrl . '/status_by_cid/' . $order['shipping_id']);

            $body = $response->json();

            if ($body['status'] === 200) {
                $order['shipping_status'] = match ($body['delivery_status']) {
                    'pending' => 'Dispatched',
                    'delivered' => 'Completed',
                    'cancelled' => 'Cancelled',
                    default => null,
                };

                if ($order['shipping_status'] === null) {
                    return;
                }

                parent::check($order);
            } else {
                dump("response body error (". $order['id'] . ") " . $body);
            }
        } catch (Throwable $e) {
            dump("throwable error (". $order['id'] . ") ". $e->getMessage());
        }
    }
}
