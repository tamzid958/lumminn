<?php

namespace App\Factories\Payment\Gateways;


use App\Contracts\Payment\PaymentGateway;
use App\Models\Order;
use App\Models\PaymentProvider;
use Illuminate\Support\Facades\Http;

class SSLCommerzGateway extends BasePaymentGateway implements PaymentGateway
{
    public function generateTransaction(array $order): void
    {
        $shipping_provider = PaymentProvider::query()->find($order['payment_provider_id']);

        $meta = $shipping_provider->meta;
        $baseUrl = $meta['baseUrl'];
        $store_id = $meta['storeId'];
        $password = $meta['password'];

        $response = Http::asForm()
            ->post($baseUrl . '/gwprocess/v4/api.php', [
                'store_id' => $store_id,
                'store_passwd' => $password,
                'total_amount' => $order['pay_amount'],
                'currency' => 'BDT',
                'tran_id' => $order['invoice_id'],
                'success_url' => env('APP_URL') . '/order/success/' . $order['invoice_id'],
                'fail_url' => env('APP_URL') . '/order/fail-or-cancel/' . $order['invoice_id'],
                'cancel_url' => env('APP_URL') . '/order/fail-or-cancel/' . $order['invoice_id'],
                'ipn_url' => env('APP_URL') . '/api/payment/sslcommerz/ipn',
                'cus_name' => $order['name'],
                'cus_email' => 'customer-bill@lumminn.com',
                'cus_add1' => $order['address'],
                'cus_city' => 'Dhaka',
                'cus_country' => 'Bangladesh',
                'cus_phone' => $order['phone_number'],
                'shipping_method' => 'NO',
                'product_name' => 'Lumminn Sunglasses and Eyeglasses',
                'product_category' => 'Fashion',
                'product_profile' => 'physical-goods'
            ]);

        $body = $response->json();

        $order['payment_id'] = $body['sessionkey'];

        $order['gateway_response'] = $body;

        parent::generateTransaction($order);
    }

    public function verify(string $invoice_id, array $order): void
    {
        if (!isset($order)) return;

        $payment_provider = PaymentProvider::query()->find($order['payment_provider_id']);

        $meta = $payment_provider->meta;
        $baseUrl = $meta['baseUrl'];
        $store_id = $meta['storeId'];
        $password = $meta['password'];

        $response = Http::get($baseUrl . '/validator/api/merchantTransIDvalidationAPI.php', [
            'tran_id' => $order['invoice_id'],
            'store_id' => $store_id,
            'store_passwd' => $password,
            'format' => 'json',
        ]);

        $body = $response->json();

        if (isset($body['element']) && !empty($body['element'])) {
            // Get the first element
            $firstElement = $body['element'][0];
            if ($order['pay_amount'] === $firstElement['amount'] && $order['invoice_id'] === $firstElement['tran_id']) {
                $order['pay_status'] = match ($firstElement['status']) {
                    'VALID', 'VALIDATED' => 'Paid',
                    'PENDING' => 'Pending',
                    'FAILED' => 'Cancelled'
                };

                $order['gateway_response'] = $body;

                if ($order['pay_status'] === 'Paid') {
                    $order['advance_amount'] = $order['pay_amount'];
                }

                parent::verify($invoice_id, $order);
            } else {
                echo "Invalid amount or invoice_id for order: " . $order['id'];
            }
        } else {
            echo "No elements found.";
        }
    }
}
