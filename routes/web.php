<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', [IndexController::class, 'index'])->name('home');
Route::post('/product/calculate', [ProductController::class, 'calculate']);
Route::get('/categories/{slug}', [CategoryController::class, 'products']);
Route::get('/products/{slug}', [ProductController::class, 'view']);
Route::get('/order/success/{invoice_id}', [OrderController::class, 'success']);
Route::get('/order/fail-or-cancel/{invoice_id}', [OrderController::class, 'failOrCancel']);