<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function products($slug): View
    {

        $category = Category::where('slug', $slug)->first();
        $products = Product::query()
        ->where('products.category_id', $category->id)
        ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
        ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
        ->orderBy('total_sold', 'desc')
        ->cursorPaginate(6);
        return view('products-by-category', compact('products', 'category'));
    }
}
