@extends('components.layouts.app', ['title' => $category->name])

@section('content')
    <div class="grid md:grid-cols-3 sm:grid-cols-2 grid-cols-1 md:gap-4 sm:gap-2 gap-4 md:px-0 px-2 max-w-7xl mx-auto mb-5">
        @foreach ($products as $product)
            <x-product-card :product='$product' />
        @endforeach
    </div>

    {{ $products->links() }}
@endsection
