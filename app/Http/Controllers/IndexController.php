<?php

namespace App\Http\Controllers;

use App\Models\BasicConfiguration;
use App\Models\Product;
use App\Models\Discount;
use App\Providers\DiscountProvider;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function index(): View
    {
        $landing_page_product_slug = BasicConfiguration::query()->where('config_key', '=', 'landing_page_product_slug')->first()->config_value;
        $product = Product::query()->where('slug', '=', $landing_page_product_slug)->first();
        return view('index', compact('product'));
    }
}
