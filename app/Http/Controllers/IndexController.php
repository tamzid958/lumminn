<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function index(): View
    {
        $product = Product::where('slug', '=', 'rayban')->first();
        return view('index', compact('product'));
    }
}
