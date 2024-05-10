<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Facades\DB;

class OrderServiceProvider
{
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

    /**
     * @throws Exception
     */
    public static function checkIfAnyFreeShippingProduct(array $data, string $action): bool
    {
        if ($action === "create") {
            $productIds = array_column($data['products'], 'id');

            return DB::table('products')
                    ->whereIn('id', $productIds)
                    ->where('is_shipping_charge_applicable', false)
                    ->count() > 0;
        } else if ($action === "edit") {
            return DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('order_items.order_id', $data['id'])
                    ->whereNotNull('order_items.product_id')
                    ->where('products.is_shipping_charge_applicable', false)
                    ->count() > 0;
        } else {
            throw new Exception("undefined action");
        }

    }
}
