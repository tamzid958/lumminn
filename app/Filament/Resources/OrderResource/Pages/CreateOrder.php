<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Providers\PaymentServiceProvider;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    private function convertToOrderItems(array $data, string $tablename): array
    {

        $productIds = array_column($data[$tablename], 'id');

        $products = DB::table($tablename)->whereIn('id', $productIds)->get()->keyBy('id');

        return collect($data[$tablename])->map(function ($product) use ($products, $tablename) {
            $productId = $product["id"];
            $quantity = $product["quantity"];

            $productData = $products->get($productId);

            if (!$productData) return null;
            return [
                'product_id' => $tablename === 'products' ? $productData->id : null,
                'optional_product_id' => $tablename === 'optional_products' ? $productData->id : null,
                'quantity' => $quantity,
                'price' => $productData->sale_price,
                'production_cost' => $productData->production_cost,
            ];

        })->filter()->toArray();
    }

    private function checkIfanyFreeShippingProduct(array $data): bool
    {
        $productIds = array_column($data['products'], 'id');

        return DB::table('products')
                ->whereIn('id', $productIds)
                ->where('is_shipping_charge_applicable', false)
                ->count() > 0;
    }

    protected function handleRecordCreation(array $data): Model
    {

        $mandatoryOrderItems = $this->convertToOrderItems($data, 'products');

        $optionalOrderItems = isset($data['optional_products']) && count($data['optional_products']) > 0 ?
            $this->convertToOrderItems($data, 'optional_products') : [];

        $orderItems = array_merge($mandatoryOrderItems, $optionalOrderItems);

        $data['total_amount'] = array_reduce($orderItems, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $freeShipping = $this->checkIfanyFreeShippingProduct($data);

        $shipping_provider = DB::table('shipping_providers')->find($data['shipping_provider_id']);

        if (!$freeShipping) {
            $data['shipping_amount'] = match ($data['shipping_class']) {
                'Inside Dhaka' => $shipping_provider->inside_dhaka_charge,
                default => $shipping_provider->outside_dhaka_charge,
            };
        } else {
            $data['shipping_amount'] = 0;
        }

        $data['pay_amount'] = $data['total_amount'] + $data['shipping_amount'] + $data['additional_amount'];

        $data['payment_id'] = PaymentServiceProvider::register($shipping_provider)->create()->generateTransaction($data);

        $record = static::getModel()::create([
            'total_amount' => $data['total_amount'],
            'additional_amount' => $data['additional_amount'],
            'shipping_amount' => $data['shipping_amount'],
            'pay_amount' => $data['pay_amount'],
            'pay_status' => $data['pay_status'],
            'shipping_status' => $data['shipping_status'],
            'shipping_class' => $data['shipping_class'],
            'payment_id' => $data['payment_id'],
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

        DB::table('order_items')->insert($orderItems);

        return $record;
    }
}
