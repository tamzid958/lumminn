<?php

namespace App\Providers;

use App\Models\IpAddress;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderServiceProvider
{
    public static function convertToOrderItem(int $product_id, int $quantity): array
    {

        $product = Product::query()->find($product_id);

        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->sale_price,
            'production_cost' => $product->production_cost,
        ];
    }

    public static function convertToOrderItems(array $data, string $tablename): array
    {

        $productIds = array_column($data[$tablename], 'id');

        $products = DB::table($tablename)->whereIn('id', $productIds)->get()->keyBy('id');

        return collect($data[$tablename])->map(function ($product) use ($tablename, $products) {
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

    public static function checkIfFreeShippingProduct(int $product_id): bool
    {
        return Product::query()
            ->where('id', $product_id)
            ->where('is_shipping_charge_applicable', false)
            ->exists();
    }

    public static function checkFakeOrder(string $ip): IpAddress
    {
        $alreadyStoredIp = IpAddress::query()->where('ip', $ip)->first();

        if (isset($alreadyStoredIp)) {
            $alreadyStoredIp->count = $alreadyStoredIp->count + 1;
            $alreadyStoredIp->save();
            return $alreadyStoredIp;
        } else {
            $ipAddress = new IpAddress();
            $ipAddress->ip = $ip;
            $ipAddress->count = 1;
            $ipAddress->is_blocked = false;
            $ipAddress->save();
            return $ipAddress;
        }
    }

    public static function checkIfAnyFreeShippingProduct(array $data): bool
    {
        $productIds = array_column($data['products'], 'id');

        return Product::query()
                ->whereIn('id', $productIds)
                ->where('is_shipping_charge_applicable', false)
                ->count() > 0;
    }
}
