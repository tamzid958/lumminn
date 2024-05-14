@extends('components.layouts.app', ['title' => 'Sunglasses and Eyewear'])


@section('content')
    <x-funnel-checkout :product='$product'/>
@endsection
