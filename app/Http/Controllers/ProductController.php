<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShippingProvider;
use App\Providers\DiscountProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Illuminate\View\View;
use Combindma\FacebookPixel\Facades\MetaPixel;

class ProductController extends Controller
{

    public function view(Request $request, $slug): View
    {
        $product = Product::where('slug', $slug)->first();

        try {
            $eventId = uniqid('ViewContent_', true);
            MetaPixel::track('ViewContent', [
                'fbc' => $request->cookie('_fbc'),
                'fbp' => $request->cookie('_fbp'),
                'currency' => 'BDT',
                'value' => $product->sale_price,
                'product' => $product->slug
            ], $eventId);
        } catch (Exception $e) {
        }

        return view('product', compact('product'));
    }

    public function calculate(Request $request)
    {
        $locale = app()->getLocale();

        // Validate the request
        $request->validate([
            'quantity' => 'required|integer|min:1|max:5',
            'id' => 'required|numeric|min:0',
            'shipping_class' => 'string',
            'coupon_code'=> 'string|nullable'
        ]);

        // Get the quantity and sale price from the request
        $quantity = $request->input('quantity');
        $product_id = $request->input('id');
        $shipping_class = $request->input('shipping_class');
        $coupon_code = $request->input('coupon_code');
       
        $product = Product::query()->find($product_id);

        $inside_dhaka_max_charge = ShippingProvider::query()->max('inside_dhaka_charge');
        $outside_dhaka_max_charge = ShippingProvider::query()->max('outside_dhaka_charge');

        $subTotal = $quantity * $product->sale_price;

        $shipping_charge = match ($shipping_class) {
            'inside-dhaka' => $inside_dhaka_max_charge,
            'outside-dhaka' => $outside_dhaka_max_charge,
        };

        $discount_amount = 0;
        $free_shipping = false;

        if(isset($coupon_code) && $coupon_code != '') {
            $discount_amount = DiscountProvider::discountAmount($coupon_code, $product, $quantity);
            $free_shipping = DiscountProvider::getDiscount($coupon_code, $product)->free_shipping ?? false;
        }
      
    
        // Return the total price as a JSON response
        return response()->json([
            'sub_total' => $subTotal,
            'shipping_charge' => $shipping_charge,
            'discount_amount' => $discount_amount,
            'free_shipping' => $free_shipping,
            'total' => $free_shipping ? $subTotal - $discount_amount : $subTotal + $shipping_charge - $discount_amount,
          ]);
    }
}
