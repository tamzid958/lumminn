@extends('components.layouts.app', [
    'title' => $product->name,
    'description' => $product->description,
    'main_photo' => $product->main_photo,
    'robots' => true,
])


@section('content')
    <x-funnel-checkout :product='$product' :order_token='$order_token' />
@endsection
