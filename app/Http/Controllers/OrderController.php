<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentProvider;
use App\Models\Product;
use App\Models\ShippingProvider;
use App\Providers\DiscountProvider;
use App\Providers\OrderServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Utils\StringUtil;
use Exception;
use Illuminate\Http\Request;
use Combindma\FacebookPixel\Facades\MetaPixel;

class OrderController extends Controller
{
    public function success($invoice_id, Request $request)
    {
        $order = Order::where('invoice_id', '=', $invoice_id)->first();
        if ($request->method() == "POST") {
            $payment_provider = PaymentProvider::find($order->payment_provider_id);

            PaymentServiceProvider::register($payment_provider)->create()->verify($order->invoice_id, $order->toArray());
        }
        try {
            MetaPixel::track('Purchase', [
                'currency' => 'BDT', 
                'value' => $order->total_amount,
            ], $order->invoice_id);
        }catch (Exception $e) {
        }
       
        return view("order-success", compact("order"));
    }

    public function fake_success($invoice_id)
    {
        return view("order-fake-success");
    }

    public function create(Request $request)
    {
            $request['phone_number'] = StringUtil::convertBanglaToEnglishPhoneNumber($request->phone_number);

            $request->validate([
                'name' => 'required|string',
                'phone_number' => 'required|numeric|digits:11',
                'address' => 'required|string',
                'shipping_class' => 'required|string',
                'payment_provider' => 'required|string',
                'quantity' => 'required|numeric|min:1|max:5',
                'product_id' => 'required|numeric'
            ]);
            
            $ipAddress = OrderServiceProvider::checkFakeOrder($request->ip());
            
            if($ipAddress->is_blocked) {
                return redirect('/order/success_/'. uniqid());
            }
      
            $productId = $request->input('product_id');
            $shippingClass = $request->input('shipping_class');
            $paymentProvider = $request->input('payment_provider');

            $orderItem = OrderServiceProvider::convertToOrderItem($productId, $request->input('quantity'));

            $order = new Order();

            $product = Product::query()->find($productId);

            $order->total_amount = $orderItem['price'] * $orderItem['quantity'];
            $order->additional_amount = 0;
            $order->discount_amount = DiscountProvider::discountAmount($product, $orderItem['quantity']);

            $freeShipping = OrderServiceProvider::checkIfFreeShippingProduct($productId);

            $shippingProviders = ShippingProvider::query()->where('slug', '<>', 'pickup');

            $shipping_provider = match ($shippingClass) {
                "inside-dhaka" => $shippingProviders->where('inside_dhaka_charge', $shippingProviders->min('inside_dhaka_charge'))->first(),
                default => $shippingProviders->where('outside_dhaka_charge', $shippingProviders->min('outside_dhaka_charge'))->first()
            };

            $order->shipping_provider_id = $shipping_provider['id'];

            if ($freeShipping) {
                $order->shipping_amount = 0;
            } else {
                $order->shipping_amount = match ($shippingClass) {
                    "inside-dhaka" => $shipping_provider->inside_dhaka_charge,
                    default => $shipping_provider->outside_dhaka_charge
                };
            }


            $order->pay_status = 'Pending';

            $order->shipping_status = 'On Hold';
            $order->shipping_class = match ($shippingClass) {
                "inside-dhaka" => 'Inside Dhaka',
                default => 'Outside Dhaka'
            };

            $order->name = $request->input('name');
            $order->phone_number = $request->input('phone_number');
            $order->address = $request->input('address');

            $payment_provider = match ($paymentProvider) {
                "online-payment" => PaymentProvider::query()->where('slug', '=', 'sslcommerz')->first(),
                default => PaymentProvider::query()->where('slug', '=', 'cash-on-delivery')->first(),

            };

            $order->payment_provider_id = $payment_provider['id'];

            $order->ip_address_id = $ipAddress->id;

            $createdOrder = Order::create($order->toArray());

            $orderItem['order_id'] = $createdOrder->id;

            OrderItem::create($orderItem);

            PaymentServiceProvider::register($payment_provider)->create()->generateTransaction($createdOrder->toArray());

            if ($paymentProvider === 'cash-on-delivery') {
                return redirect('/order/success/' . $createdOrder->invoice_id);
            } else {
                $order = Order::find($createdOrder->id);

                if (!empty($order->gateway_response) && isset($order->gateway_response['GatewayPageURL'])) {
                    // Gateway response is not empty and contains the GatewayPageURL property
                    $gatewayPageURL = $order->gateway_response['GatewayPageURL'];

                    // Redirect the user to the GatewayPageURL
                    return redirect($gatewayPageURL);
                } else {
                    // Either gateway response is empty or does not contain the GatewayPageURL property
                    // Handle the case accordingly, e.g., display an error message
                    return redirect('/order/fail-or-cancel/' . $createdOrder->invoice_id);
                }
            }
    
    }

    public function failOrCancel($invoice_id, Request $request)
    {
        if ($request->method() == "POST") {
            $order = Order::where('invoice_id', '=', $invoice_id)->first();
            $payment_provider = PaymentProvider::find($order->payment_provider_id);

            PaymentServiceProvider::register($payment_provider)->create()->verify($order->invoice_id, $order->toArray());
        }
        return view("order-fail-or-cancel");
    }
}
