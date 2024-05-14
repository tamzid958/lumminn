<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentProvider;
use App\Providers\PaymentServiceProvider;
use Illuminate\Http\Request;

class SSLCommerzController extends Controller
{
    public function ipn(Request $request)
    {
        $data = $request->all();
        $invoice_id = $data['tran_id'];

        $shipping_provider = PaymentProvider::query()->where('slug', '=', 'sslcommerz')->first();
        PaymentServiceProvider::register($shipping_provider)->create()->verify($invoice_id);

        return response($invoice_id, 200)->header('Content-Type', 'application/text');
    }
}
