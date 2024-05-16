@extends('components.layouts.app', [
    'title' => 'Sunglasses and Eyewear',
    'description' => "See and feel with us",
    'main_photo' => "landing_page_cover.png",
    'robots' => true,
])


@section('content')
    <x-funnel-checkout :product='$product'/>
@endsection
