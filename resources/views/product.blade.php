@extends('components.layouts.app', [
    'title' => $product->name,
    'description' => $product->description,
    'main_photo' => $product->main_photo,
])


@section('content')
    <x-funnel-checkout :product='$product' />
@endsection
