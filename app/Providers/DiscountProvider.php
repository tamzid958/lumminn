<?php

namespace App\Providers;

use App\Models\Discount;
use App\Models\Enum\DiscountType;
use App\Models\Product;

class DiscountProvider
{

    public static function priceAfterDiscount(Product $product, $quantity = 1)
    {
        $discount = DiscountProvider::getDiscount($product);

        $after_discount_price = $quantity * $product->sale_price;

        if (isset($discount)) {
            if ($discount->type === DiscountType::Flat) {
                $after_discount_price = ($product->sale_price - $discount->value) * $quantity;
            } elseif ($discount->type === DiscountType::Percentage) {
                $after_discount_price = ($product->sale_price - ($product->sale_price * $discount->value) / 100) * $quantity;
            }
        }

        return $after_discount_price;
    }

    public static function getDiscount(Product $product): Discount|null
    {
        return Discount::query()->where('product_id', $product->id)->where('active', true)->first();
    }

    public static function discountAmount(Product $product, $quantity = 1)
    {
        $discount = DiscountProvider::getDiscount($product);

        $discount_amount = 0;

        if (isset($discount)) {
            if ($discount->type === DiscountType::Flat) {
                $discount_amount = $discount->value * $quantity;
            } elseif ($discount->type === DiscountType::Percentage) {
                $discount_amount = (($product->sale_price * $discount->value) / 100) * $quantity;
            }
        }

        return $discount_amount;
    }
}
