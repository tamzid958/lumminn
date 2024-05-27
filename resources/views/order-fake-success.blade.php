@extends('components.layouts.app', ['title' => 'Order Successful'])

@php
    use Illuminate\Support\Number;
    $locale = app()->getLocale();

    $messenger_link =
        App\Models\BasicConfiguration::query()->where('config_key', '=', 'messenger_link')->first()->config_value ??
        null;
    $facebook_group_link =
        App\Models\BasicConfiguration::query()->where('config_key', '=', 'facebook_group_link')->first()
            ->config_value ?? null;
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
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">{{ __('order_placed') }}</h3>
                <p class="text-gray-600 my-2">{{ __('thanks_after_order') }}</p>
                <p> {{ __('your_order_is') }} #{{ Number::format(rand(1000, 9999), locale: $locale) }} </p>
                @isset($messenger_link)
                    <div class="py-3 text-center">
                        <a href="{{ $messenger_link }}" class="btn btn-secondary" target="_blank">
                            {{ __('want_to_add_power') }}
                        </a>
                    </div>
                @endisset
                @isset($facebook_group_link)
                    <div class="py-3 text-center">
                        <a href="{{ $facebook_group_link }}" class="btn btn-secondary" target="_blank">
                            {{ __('facebook_group') }}
                        </a>
                    </div>
                @endisset
                <div class="py-2 mt-5 text-center">
                    <a href="/" class="link-primary">
                        {{ __('go_back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
