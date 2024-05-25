<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShippingProvider;
use App\Providers\DiscountProvider;
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
            ], $eventId);
        }catch (\Exception $e) {
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
            'shipping_class' => 'string'
        ]);

        // Get the quantity and sale price from the request
        $quantity = $request->input('quantity');
        $product_id = $request->input('id');
        $shipping_class = $request->input('shipping_class');

        $product = Product::query()->find($product_id);

        $inside_dhaka_max_charge = ShippingProvider::query()->max('inside_dhaka_charge');
        $outside_dhaka_max_charge = ShippingProvider::query()->max('outside_dhaka_charge');

        $subTotal = DiscountProvider::priceAfterDiscount($product, $quantity);

        if (!$product->is_shipping_charge_applicable) {
            $shipping_charge = Lang::get('free_delivery', locale: $locale);
            $total = Number::currency($subTotal, in: 'BDT', locale: $locale);
        } else {
            $shipping_charge = match ($shipping_class) {
                'inside-dhaka' => Number::currency($inside_dhaka_max_charge, in: 'BDT', locale: $locale),
                'outside-dhaka' => Number::currency($outside_dhaka_max_charge, in: 'BDT', locale: $locale),
                default => Lang::get('will_be_calculated', locale: $locale)
            };
            $total = match ($shipping_class) {
                'inside-dhaka' => Number::currency($subTotal + $inside_dhaka_max_charge, in: 'BDT', locale: $locale),
                'outside-dhaka' => Number::currency($subTotal + $outside_dhaka_max_charge, in: 'BDT', locale: $locale),
                default => Lang::get('will_be_calculated', locale: $locale)
            };
        }


        // Return the total price as a JSON response
        return response()->json([
            'sub_total' => Number::currency($subTotal, in: 'BDT', locale: $locale),
            'shipping_charge' => $shipping_charge,
            'total' => $total
        ]);
    }
}
