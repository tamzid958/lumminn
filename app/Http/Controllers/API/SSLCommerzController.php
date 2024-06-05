<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Providers\PaymentServiceProvider;
use Illuminate\Http\Request;

class SSLCommerzController extends Controller
{
    public function ipn(Request $request)
    {
        $data = $request->all();
        $invoice_id = $data['tran_id'];
        $order = Order::where('invoice_id', $invoice_id)->first();

        $payment_provider = PaymentProvider::query()->where('slug', '=', 'sslcommerz')->first();

        PaymentServiceProvider::register($payment_provider)->create()->verify($invoice_id, $order->toArray());

        return response($invoice_id, 200)->header('Content-Type', 'application/text');
    }
}
