<?php

use App\Http\Controllers\API\SSLCommerzController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::post('/payment/sslcommerz/ipn', [SSLCommerzController::class, 'ipn']);
