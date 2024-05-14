@extends('components.layouts.app', ['title' => $product->name])


@section('content')
    <x-funnel-checkout :product='$product'/>
@endsection
