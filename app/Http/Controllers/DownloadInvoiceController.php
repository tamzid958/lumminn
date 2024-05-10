<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadInvoiceController extends Controller
{
    //

    public function download(array $orders)
    {
        dump($orders);
    }
}
