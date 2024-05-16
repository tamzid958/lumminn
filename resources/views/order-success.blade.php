@extends('components.layouts.app', ['title' => 'Order Successful'])

@php
    $messenger_link =
        App\Models\BasicConfiguration::query()->where('config_key', '=', 'messenger_link')->first()->config_value ??
        null;
@endphp
@section('content')
    <div class="lg:px-20 xl:px-32 max-w-7xl mx-auto rounded-md card bg-base-200 pb-4">
        <div class="p-6 mx-auto rounded-md">
            <svg viewBox="0 0 24 24" class="w-16 h-16 mx-auto my-6 text-primary">
                <path fill="currentColor"
                      d="M12,0A12,12,0,1,0,24,12,12.014,12.014,0,0,0,12,0Zm6.927,8.2-6.845,9.289a1.011,1.011,0,0,1-1.43.188L5.764,13.769a1,1,0,1,1,1.25-1.562l4.076,3.261,6.227-8.451A1,1,0,1,1,18.927,8.2Z">
                </path>
            </svg>
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">Order Placed!</h3>
                <p class="text-gray-600 my-2">Thank you for placing an order.</p>
                <p> Your order is #{{ $order->id }} </p>
                @isset($messenger_link)
                    <div class="py-3 text-center">
                        <a href="{{  $messenger_link }}" class="btn btn-secondary" target="_blank">
                            Want to add power to the glasses?
                        </a>
                    </div>
                @endisset
                <div class="py-2 mt-5 text-center">
                    <a href="/" class="link-primary">
                        GO BACK
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
