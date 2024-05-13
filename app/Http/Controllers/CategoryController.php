<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function products($slug): View {
        $category = Category::where('slug', $slug)->first();
        $products = $category->products()->get();
        return view('products-by-category', compact('products', 'category'));
    }
}
