@extends('components.layouts.app', ['title' => $category->name])


@section('content')
    <div class="grid grid-cols-3 gap-4 max-w-7xl mx-auto">
        @foreach ($products as $product)
            <x-product-card :product='$product'/>
        @endforeach
    </div>

    {{ $products->links() }}
@endsection
