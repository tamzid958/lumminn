<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::middleware([('lscache:max-age=31536000;public;esi=on')])->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('home');
    Route::get('/categories/{slug}', [CategoryController::class, 'products']);
    Route::get('/products/{slug}', [ProductController::class, 'view']);
    Route::post('/product/calculate', [ProductController::class, 'calculate']);
});

Route::post('/order/create', [OrderController::class, 'create']);
Route::any('/order/success/{invoice_id}', [OrderController::class, 'success'])->withoutMiddleware([VerifyCsrfToken::class]);
Route::any('/order/fail-or-cancel/{invoice_id}', [OrderController::class, 'failOrCancel'])->withoutMiddleware([VerifyCsrfToken::class]);


