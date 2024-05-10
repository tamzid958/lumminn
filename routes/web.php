<?php

use App\Http\Controllers\DownloadInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/download-invoice',
    [DownloadInvoiceController::class, 'download']
)->name('invoices.pdf.download');
