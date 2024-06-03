<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function products($slug): View
    {

        $category = Category::where('slug', $slug)->first();
        $products = Product::query()
            ->select('products.*')
            ->selectRaw('(SELECT COUNT(*) FROM order_items WHERE order_items.product_id = products.id) AS orders_count')
            ->where('category_id', $category->id)
            ->orderByDesc('orders_count')
            ->cursorPaginate(6);
        return view('products-by-category', compact('products', 'category'));
    }
}
