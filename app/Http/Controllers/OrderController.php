<?php

namespace App\Http\Controllers;

use App\Models\BasicConfiguration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentProvider;
use App\Models\Product;
use App\Models\ShippingProvider;
use App\Providers\DiscountProvider;
use App\Providers\OrderServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Utils\StringUtil;
use Combindma\FacebookPixel\Facades\MetaPixel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function success($invoice_id, Request $request)
    {
        $order = Order::where('invoice_id', '=', $invoice_id)->first();
        if ($request->method() == "POST") {
            $payment_provider = PaymentProvider::find($order->payment_provider_id);
            PaymentServiceProvider::register($payment_provider)->create()->setPaymentId($order->invoice_id, $request->input('val_id'));
        }
        try {
            MetaPixel::track('Purchase', [
                'currency' => 'BDT',
                'value' => $order->total_amount,
            ], $order->invoice_id);
        } catch (Exception $e) {
        }

        return view("order-success", compact("order"));
    }

    public function fake_success($invoice_id)
    {
        return view("order-fake-success");
    }

    public function failOrCancel($invoice_id, Request $request)
    {
        if ($request->method() == "POST") {
            $order = Order::where('invoice_id', '=', $invoice_id)->first();
            $payment_provider = PaymentProvider::find($order->payment_provider_id);

            PaymentServiceProvider::register($payment_provider)->create()->setPaymentId($order->invoice_id, $request->input('val_id'));
        }
        return view("order-fail-or-cancel");
    }

    public function create(Request $request)
    {
        try {
            // Convert phone number from Bangla to English
            $request['phone_number'] = StringUtil::convertBanglaToEnglishPhoneNumber($request->phone_number);

            // Validate the request data
            $request->validate([
                'name' => 'required|string',
                'phone_number' => 'required|numeric|digits:11',
                'address' => 'required|string',
                'shipping_class' => 'required|string',
                'payment_provider' => 'required|string',
                'quantity' => 'required|numeric|min:1|max:5',
                'product_id' => 'required|numeric',
            ]);

            // Validate and invalidate order token
            if (!$this->validateAndInvalidateToken($request->input('order_token'))) {
                return redirect('/order/success_/' . uniqid());
            }

            // Check for fake order by IP address
            $ipAddress = OrderServiceProvider::checkFakeOrder($request->ip());
            if ($ipAddress->is_blocked) {
                return redirect('/order/success_/' . uniqid());
            }

            // Prepare and create the order
            $orderData = $this->prepareOrderData($request, $ipAddress);
            $createdOrder = Order::create($orderData);

            // Create the order item
            $this->createOrderItem($createdOrder->id, $request->input('product_id'), $request->input('quantity'));

            // Register payment
            $paymentProviderSlug = $this->registerPayment($createdOrder, $request->input('payment_provider'));

            // Handle the payment redirect
            return redirect($this->handlePaymentRedirect($createdOrder, $paymentProviderSlug));
        } catch (Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function validateAndInvalidateToken($orderToken)
    {
        if (session('order_token') !== $orderToken) {
            return false;
        }
        session()->forget('order_token');
        return true;
    }

    private function prepareOrderData(Request $request, $ipAddress)
    {
        $product = Product::findOrFail($request->input('product_id'));
        $orderItem = OrderServiceProvider::convertToOrderItem($product->id, $request->input('quantity'));

        $shippingProvider = $this->getShippingProvider($request->input('shipping_class'));
        $couponCode = $request->input('coupon_code');
        $discountData = $this->getDiscountData($couponCode, $product, $orderItem['quantity'], $shippingProvider->charge);

        return [
            'name' => $request->input('name'),
            'phone_number' => $request->input('phone_number'),
            'address' => $request->input('address'),
            'geo_location' => $request->input('geo_location'),
            'total_amount' => $orderItem['price'] * $orderItem['quantity'],
            'additional_amount' => 0,
            'shipping_provider_id' => $shippingProvider->id,
            'shipping_amount' => $shippingProvider->charge,
            'discount_amount' => $discountData['discount_amount'],
            'pay_status' => 'Pending',
            'shipping_status' => 'On Hold',
            'shipping_class' => $this->getShippingClassLabel($request->input('shipping_class')),
            'payment_provider_id' => $this->getPaymentProviderId($request->input('payment_provider')),
            'ip_address_id' => $ipAddress->id,
        ];
    }

    private function getShippingProvider($shippingClass)
    {
        $shippingProviders = ShippingProvider::where('slug', '<>', 'pickup');
        if ($shippingClass === "inside-dhaka") {
            $provider = $shippingProviders->orderBy('inside_dhaka_charge')->first();
            $charge = $provider->inside_dhaka_charge;
        } else {
            $provider = $shippingProviders->orderBy('outside_dhaka_charge')->first();
            $charge = $provider->outside_dhaka_charge;
        }
        $provider->charge = $charge;
        return $provider;
    }

    private function getDiscountData($couponCode, $product, $quantity, $shippingCharge)
    {
        if (empty($couponCode)) {
            return ['discount_amount' => 0, 'free_shipping' => false];
        }

        $discountAmount = DiscountProvider::discountAmount($couponCode, $product, $quantity);
        $freeShipping = DiscountProvider::getDiscount($couponCode, $product)->free_shipping ?? false;

        return [
            'discount_amount' => $discountAmount + ($freeShipping ? $shippingCharge : 0),
            'free_shipping' => $freeShipping
        ];
    }

    private function getShippingClassLabel($shippingClass)
    {
        return $shippingClass === "inside-dhaka" ? 'Inside Dhaka' : 'Outside Dhaka';
    }

    private function getPaymentProviderId($paymentProvider)
    {
        $defaultOnlinePayment = BasicConfiguration::where('config_key', 'online-payment')->value('config_value');
        return $paymentProvider === "online-payment"
            ? PaymentProvider::where('slug', $defaultOnlinePayment)->value('id')
            : PaymentProvider::where('slug', 'cash-on-delivery')->value('id');
    }

    private function createOrderItem($orderId, $productId, $quantity)
    {
        $orderItem = OrderServiceProvider::convertToOrderItem($productId, $quantity);
        $orderItem['order_id'] = $orderId;
        OrderItem::create($orderItem);
    }

    private function registerPayment($order, $paymentProvider)
    {
        $paymentProviderInstance = PaymentServiceProvider::register($paymentProvider)->create();
        $paymentProviderInstance->generateTransaction($order->toArray());
        return $paymentProvider;
    }

    private function handlePaymentRedirect($order, $paymentProviderSlug)
    {
        if ($paymentProviderSlug === 'cash-on-delivery') {
            return '/order/success/' . $order->invoice_id;
        }

        $order = Order::find($order->id);
        if (!empty($order->gateway_response) && isset($order->gateway_response['GatewayPageURL'])) {
            return $order->gateway_response['GatewayPageURL'];
        }

        return '/order/fail-or-cancel/' . $order->invoice_id;
    }

}
