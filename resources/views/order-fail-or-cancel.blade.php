@extends('components.layouts.app', ['title' => 'Order Successful'])

@php
    $facebook_group_link =
        App\Models\BasicConfiguration::query()->where('config_key', '=', 'facebook_group_link')->first()
            ->config_value ?? null;
@endphp

@section('content')
    <div class="lg:px-20 xl:px-32 max-w-7xl mx-auto rounded-md card bg-base-200 pb-4">
        <div class="p-6 mx-auto rounded-md">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" id="Cancel" class="w-16 h-16 mx-auto my-6">
                <path
                    d="M24 4C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm10 27.17L31.17 34 24 26.83 16.83 34 14 31.17 21.17 24 14 16.83 16.83 14 24 21.17 31.17 14 34 16.83 26.83 24 34 31.17z"
                    fill="#d85b53" class="color000000 svgShape"></path>
                <path fill="none" d="M0 0h48v48H0z"></path>
            </svg>
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">{{ __('order_failed') }}</h3>
                <p class="text-gray-600 my-2">{{ __('try_again') }}</p>
                @isset($facebook_group_link)
                    <div class="py-3 text-center">
                        <a href="{{ $facebook_group_link }}" class="btn btn-secondary" target="_blank">
                            {{ __('facebook_group') }}
                        </a>
                    </div>
                @endisset
                <div class="py-10 mt-5 text-center">
                    <a href="/" class="link-primary">
                        {{ __('go_back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
