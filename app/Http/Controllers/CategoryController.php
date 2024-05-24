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
            ->where('category_id', $category->id)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(6);
        return view('products-by-category', compact('products', 'category'));
    }
}
