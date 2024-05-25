<?php

namespace App\Http\Controllers;

use App\Models\BasicConfiguration;
use App\Models\Product;
use Illuminate\View\View;
use Combindma\FacebookPixel\Facades\MetaPixel;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request): View
    {
        $landing_page_product_slug = BasicConfiguration::query()->where('config_key', '=', 'landing_page_product_slug')->first()->config_value;
        $product = Product::query()->where('slug', '=', $landing_page_product_slug)->first();

        try {
            MetaPixel::track('ViewContent', [
             'fbc' => $request->cookie('_fbc'),
             'fbp' => $request->cookie('_fbp'),
             'currency' => 'BDT', 
             'value' => $product->sale_price,
            ], $product->slug);
        }catch (\Exception $e) {
        }

        return view('index', compact('product'));
    }
}
