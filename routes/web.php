<?php

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', [IndexController::class, 'index']);
