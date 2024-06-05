<?php

namespace App\Providers;

use App\Models\Discount;
use App\Models\Enum\DiscountType;
use App\Models\Product;

class DiscountProvider
{
    public static function getDiscount(string $code, Product $product): Discount|null
    {
        $discount= Discount::query()->whereRaw('LOWER(code) = ?', [strtolower($code)])->where('active', true)->first();

        if (isset($discount) && $discount->products()->where('product_id', $product->id)->exists()) {
            return $discount;
        }
        return null;
    }

    public static function discountAmount(string $code, Product $product, $quantity = 1)
    {
        $discount = DiscountProvider::getDiscount($code, $product);
        
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
