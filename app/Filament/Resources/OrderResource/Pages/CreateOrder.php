<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderItem;
use App\Models\PaymentProvider;
use App\Models\ShippingProvider;
use App\Providers\OrderServiceProvider;
use App\Providers\PaymentServiceProvider;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {

        $mandatoryOrderItems = OrderServiceProvider::convertToOrderItems($data, 'products');

        $optionalOrderItems = isset($data['optional_products']) && count($data['optional_products']) > 0 ?
            OrderServiceProvider::convertToOrderItems($data, 'optional_products') : [];

        $orderItems = array_merge($mandatoryOrderItems, $optionalOrderItems);

        $data['total_amount'] = array_reduce($orderItems, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $freeShipping = OrderServiceProvider::checkIfAnyFreeShippingProduct($data);

        $shipping_provider = ShippingProvider::query()->find($data['shipping_provider_id']);

        if (!$freeShipping) {
            $data['shipping_amount'] = match ($data['shipping_class']) {
                'Inside Dhaka' => $shipping_provider->inside_dhaka_charge,
                default => $shipping_provider->outside_dhaka_charge,
            };
        } else {
            $data['shipping_amount'] = 0;
        }

        $record = static::getModel()::create([
            'total_amount' => $data['total_amount'],
            'additional_amount' => $data['additional_amount'],
            'discount_amount' => $data['discount_amount'],
            'advance_amount' => $data['advance_amount'],
            'shipping_amount' => $data['shipping_amount'],
            'pay_status' => $data['pay_status'],
            'shipping_status' => $data['shipping_status'],
            'shipping_class' => $data['shipping_class'],
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'address' => $data['address'],
            'shipping_provider_id' => $data['shipping_provider_id'],
            'payment_provider_id' => $data['payment_provider_id'],
            'note' => $data['note'],
            'attachment' => $data['attachment'],
        ]);


        $orderItems = collect($orderItems)->map(function ($orderItem) use ($record) {
            return [
                'order_id' => $record->id,
                'product_id' => $orderItem['product_id'],
                'optional_product_id' => $orderItem['optional_product_id'],
                'quantity' => $orderItem['quantity'],
                'price' => $orderItem['price'],
                'production_cost' => $orderItem['production_cost'],
            ];
        })->toArray();

        OrderItem::query()->insert($orderItems);

        $payment_provider = PaymentProvider::query()->find($data['payment_provider_id']);

        PaymentServiceProvider::register($payment_provider)->create()->generateTransaction($record->toArray());

        return $record;
    }
}
