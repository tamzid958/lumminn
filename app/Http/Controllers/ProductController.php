<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShippingProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{

    public function view($slug): View
    {
        $product = Product::where('slug', $slug)->first();
        return view('product', compact('product'));
    }

    public function calculate(Request $request)
    {
        // Validate the request
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
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

        $subTotal = $quantity * $product->sale_price;

        if (!$product->is_shipping_charge_applicable) {
            $shipping_charge = 'Free Delivery';
            $total = "৳ " . $subTotal;
        } else {
            $shipping_charge = match ($shipping_class) {
                'inside-dhaka' => "৳ " . $inside_dhaka_max_charge,
                'outside-dhaka' => "৳ " . $outside_dhaka_max_charge,
                default => 'will be calculated'
            };
            $total = match ($shipping_class) {
                'inside-dhaka' => "৳ " . ($subTotal + $inside_dhaka_max_charge),
                'outside-dhaka' => "৳ " . ($subTotal + $outside_dhaka_max_charge),
                default => 'will be calculated'
            };
        }


        // Return the total price as a JSON response
        return response()->json([
            'sub_total' => $subTotal,
            'shipping_charge' => $shipping_charge,
            'total' => $total
        ]);
    }
}
