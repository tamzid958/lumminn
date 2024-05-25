<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="lemonade">
@php
    $locale = app()->getLocale();
    $categories = App\Models\Category::query()->where('parent_id', '=', null)->get();
    $facebook_page_link =
        App\Models\BasicConfiguration::query()->where('config_key', '=', 'facebook_page_link')->first()->config_value ??
        null;
@endphp

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($locale == 'bn')
        <style>
            html {
                font-family: web-bengali, serif;
            }
        </style>
    @endif
    <title>Lumminn | {{ $title ?? 'Page Title' }}</title>
    <meta property="og:title" content="Lumminn | {{ $title ?? 'Page Title' }}">

    @isset($robots)
        <meta name="robots" content="index, follow" />
    @endisset

    @isset($description)
        <meta name="description" content="{{ $description }}">
        <meta property="og:description" content="{{ $description }}">
    @endisset

    @isset($main_photo)
        <meta property="og:image" content="{{ asset('storage/' . $main_photo) }}">
    @endisset
    <x-metapixel-head />
</head>
<main class="flex flex-col h-screen justify-between">
    <header>
        <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-6" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href='{{ route('home') }}' class="-m-1.5 p-1.5">
                    <span class="sr-only">Lumminn</span>
                    <img class="h-14 w-auto" src="{{ asset('logo.light.png') }}" alt="Lumminn">
                </a>
            </div>

            <div class="mobile-menu lg:hidden relative">
                <input type="checkbox" id="menu-toggle" class="hidden">
                <label for="menu-toggle" class="menu-icon block cursor-pointer px-4 py-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </label>
                <div
                    class="menu hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-base-100 ring-1 ring-black ring-opacity-5 z-20">
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="menu-button"
                        tabindex="-1">
                        @foreach ($categories as $category)
                            <a href="/categories/{{ $category->slug }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-base-300 hover:text-gray-900 rounded-md"
                                role="menuitem">{{ $category->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>


            <div class="gap-x-12 md:flex hidden">
                @foreach ($categories as $category)
                    <a href="/categories/{{ $category->slug }}"
                        class="text-sm font-semibold leading-6 underline underline-offset-2">{{ $category->name }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <main class="mb-auto">
        <x-metapixel-body />
        @yield('content')
    </main>

    <footer class="footer items-center p-4 bg-neutral text-neutral-content mt-5 flex justify-between bg-opacity-80">
        <aside class="items-center grid-flow-col">
            <svg width="36" height="36" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                fill-rule="evenodd" clip-rule="evenodd" class="fill-current">
                <path
                    d="M22.672 15.226l-2.432.811.841 2.515c.33 1.019-.209 2.127-1.23 2.456-1.15.325-2.148-.321-2.463-1.226l-.84-2.518-5.013 1.677.84 2.517c.391 1.203-.434 2.542-1.831 2.542-.88 0-1.601-.564-1.86-1.314l-.842-2.516-2.431.809c-1.135.328-2.145-.317-2.463-1.229-.329-1.018.211-2.127 1.231-2.456l2.432-.809-1.621-4.823-2.432.808c-1.355.384-2.558-.59-2.558-1.839 0-.817.509-1.582 1.327-1.846l2.433-.809-.842-2.515c-.33-1.02.211-2.129 1.232-2.458 1.02-.329 2.13.209 2.461 1.229l.842 2.515 5.011-1.677-.839-2.517c-.403-1.238.484-2.553 1.843-2.553.819 0 1.585.509 1.85 1.326l.841 2.517 2.431-.81c1.02-.33 2.131.211 2.461 1.229.332 1.018-.21 2.126-1.23 2.456l-2.433.809 1.622 4.823 2.433-.809c1.242-.401 2.557.484 2.557 1.838 0 .819-.51 1.583-1.328 1.847m-8.992-6.428l-5.01 1.675 1.619 4.828 5.011-1.674-1.62-4.829z">
                </path>
            </svg>
            <p>{{ __('copyright') }}</p>
        </aside>
        <nav class="grid-flow-col gap-4 md:place-self-center md:justify-self-end">
            @isset($facebook_page_link)
                <a href="{{ $facebook_page_link }}" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        class="fill-current">
                        <path
                            d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z">
                        </path>
                    </svg>
                </a>
            @endisset
        </nav>
    </footer>
</main>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const menuToggle = document.getElementById("menu-toggle");
        const menu = document.querySelector(".menu");

        menuToggle.addEventListener("change", function() {
            if (this.checked) {
                menu.classList.remove("hidden");
            } else {
                menu.classList.add("hidden");
            }
        });

        // Add event listener to detect clicks outside of the menu
        document.body.addEventListener("click", function(event) {
            const isClickInsideMenu = menu.contains(event.target);
            const isMenuToggle = event.target === menuToggle;

            if (!isClickInsideMenu && !isMenuToggle) {
                menu.classList.add("hidden");
                menuToggle.checked = false;
            }
        });
    });
</script>

</html>
