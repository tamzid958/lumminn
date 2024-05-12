@extends('components.layouts.app', ['title' => 'Sunglasses and Eyewear'])
@php
    function getAdjacentStrings($array, $currentString)
    {
        // Find the index of the current string
        $currentIndex = array_search($currentString, $array);

        // If the current string is not found or it's the first or last element, return null for both previous and next
    if ($currentIndex === false) {
        return ['previous' => null, 'next' => null];
    }

    // Get the previous string using array slicing or set the last item if null
    $previousString = $currentIndex > 0 ? $array[$currentIndex - 1] : end($array);

    // Get the next string using array slicing or set the first item if null
    $nextString = $currentIndex < count($array) - 1 ? $array[$currentIndex + 1] : $array[0];

    return ['previous' => $previousString, 'next' => $nextString];
    }
@endphp

@section('content')
    <div class="grid sm:px-10 lg:grid-cols-2 lg:px-20 xl:px-32 w-11/12 mx-auto rounded-md card bg-base-200">
        <div class="px-4 pt-8">
            <p class="text-xl font-medium">Order Summary</p>
            <p class="text-gray-500">Check your items. And select a suitable shipping method.</p>
            <div class="mt-8 space-y-3 rounded-lg border bg-base-300 px-2 py-3 sm:px-6">
                <div class="flex flex-col rounded-lg bg-base-400 sm:flex-row">
                    <img class="m-2 h-24 w-28 rounded-md border object-cover object-center"
                        src="{{ asset('storage/' . $product->main_photo) }}" alt="" />
                    <div class="flex w-full flex-col px-4 py-4">
                        <span class="font-semibold">{{ $product->name }}</span>

                        <div class="max-w-xs rounded-md w-fit p-1 my-1 border-black border-solid border-2">
                            <div class="relative flex items-center">
                                <button type="button" id="decrement-button" data-input-counter-decrement="counter-input"
                                    class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 inline-flex items-center justify-center border border-gray-300 rounded-md h-5 w-5 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                                    <svg class="w-2.5 h-2.5 text-gray-900 dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M1 1h16" />
                                    </svg>
                                </button>

                                <input type="text" id="counter-input" data-input-counter
                                    class="flex-shrink-0 text-gray-900 border-0 bg-transparent text-sm font-normal focus:outline-none focus:ring-0 max-w-[2.5rem] text-center"
                                    placeholder="" value="12" required />
                                <button type="button" id="increment-button" data-input-counter-increment="counter-input"
                                    class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 inline-flex items-center justify-center border border-gray-300 rounded-md h-5 w-5 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                                    <svg class="w-2.5 h-2.5 text-gray-900 dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M9 1v16M1 9h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-lg font-bold">৳ {{ $product->sale_price }}</p>
                    </div>

                    <button class="btn my-auto" onclick="see_description.showModal()">Description</button>
                    <dialog id="see_description" class="modal">
                        <div class="modal-box">
                            <h3 class="font-bold text-lg">{!! $product->name !!}</h3>
                            <p class="py-4">{!! $product->description !!}</p>
                            <div class="modal-action">
                                <form method="dialog">
                                    <button class="btn">Close</button>
                                </form>
                            </div>
                        </div>
                    </dialog>

                </div>
            </div>
            <div class="carousel w-full mt-5 bg-base-300 h-2/6 rounded-md">
                @foreach ($product->photos as $photo)
                    @php
                        $adjacentStrings = getAdjacentStrings($product->photos, $photo);
                    @endphp

                    <div id="slide-{{ $photo }}" class="carousel-item relative w-full rounded-md">
                        <img src="{{ asset('storage/' . $photo) }}" class="w-full rounded-md object-contain h-full" />
                        <div class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                            <a href="#slide-{{ $adjacentStrings['previous'] }}" class="btn btn-circle">❮</a>
                            <a href="#slide-{{ $adjacentStrings['next'] }}" class="btn btn-circle">❯</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <iframe class="w-full mt-5 bg-base-300 rounded-md" height="315"
                src="https://www.youtube.com/embed/_rBPwu2uS-w?si=5uB8_Fc69i2fo9mx" title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen autoplay controls>
            </iframe>
        </div>
        <div class="mt-10 px-4 pt-8 lg:mt-0">
            <p class="text-xl font-medium">Payment Details</p>
            <p class="text-gray-500">Complete your order by providing your payment details.</p>
            <div class="">
                <label for="name" class="mt-8 mb-2 block text-sm font-medium">Full Name</label>
                <div class="relative">
                    <input type="text" id="name" name="name"
                        class="w-full rounded-md border border-gray-200 px-4 py-3 pl-11 text-sm uppercase shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Your full name here" />
                    <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                        </svg>
                    </div>
                </div>
                <label for="email" class="mt-4 mb-2 block text-sm font-medium">Phone Number</label>
                <div class="relative">
                    <input type="tel" id="phone_number" name="phone_number"
                        class="w-full rounded-md border border-gray-200 px-4 py-3 pl-11 text-sm shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="+8801521203280" />
                    <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">

                        <?xml version="1.0" encoding="iso-8859-1"?>
                        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                        <svg fill="#000000" class="h-4 w-4 text-gray-400" version="1.1" id="Capa_1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            viewBox="0 0 473.806 473.806" xml:space="preserve">
                            <g>
                                <g>
                                    <path
                                        d="M374.456,293.506c-9.7-10.1-21.4-15.5-33.8-15.5c-12.3,0-24.1,5.3-34.2,15.4l-31.6,31.5c-2.6-1.4-5.2-2.7-7.7-4
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c-3.6-1.8-7-3.5-9.9-5.3c-29.6-18.8-56.5-43.3-82.3-75c-12.5-15.8-20.9-29.1-27-42.6c8.2-7.5,15.8-15.3,23.2-22.8
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c2.8-2.8,5.6-5.7,8.4-8.5c21-21,21-48.2,0-69.2l-27.3-27.3c-3.1-3.1-6.3-6.3-9.3-9.5c-6-6.2-12.3-12.6-18.8-18.6
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c-9.7-9.6-21.3-14.7-33.5-14.7s-24,5.1-34,14.7c-0.1,0.1-0.1,0.1-0.2,0.2l-34,34.3c-12.8,12.8-20.1,28.4-21.7,46.5
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c-2.4,29.2,6.2,56.4,12.8,74.2c16.2,43.7,40.4,84.2,76.5,127.6c43.8,52.3,96.5,93.6,156.7,122.7c23,10.9,53.7,23.8,88,26
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c2.1,0.1,4.3,0.2,6.3,0.2c23.1,0,42.5-8.3,57.7-24.8c0.1-0.2,0.3-0.3,0.4-0.5c5.2-6.3,11.2-12,17.5-18.1c4.3-4.1,8.7-8.4,13-12.9
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c9.9-10.3,15.1-22.3,15.1-34.6c0-12.4-5.3-24.3-15.4-34.3L374.456,293.506z M410.256,398.806
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    C410.156,398.806,410.156,398.906,410.256,398.806c-3.9,4.2-7.9,8-12.2,12.2c-6.5,6.2-13.1,12.7-19.3,20
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c-10.1,10.8-22,15.9-37.6,15.9c-1.5,0-3.1,0-4.6-0.1c-29.7-1.9-57.3-13.5-78-23.4c-56.6-27.4-106.3-66.3-147.6-115.6
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c-34.1-41.1-56.9-79.1-72-119.9c-9.3-24.9-12.7-44.3-11.2-62.6c1-11.7,5.5-21.4,13.8-29.7l34.1-34.1c4.9-4.6,10.1-7.1,15.2-7.1
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c6.3,0,11.4,3.8,14.6,7c0.1,0.1,0.2,0.2,0.3,0.3c6.1,5.7,11.9,11.6,18,17.9c3.1,3.2,6.3,6.4,9.5,9.7l27.3,27.3
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c10.6,10.6,10.6,20.4,0,31c-2.9,2.9-5.7,5.8-8.6,8.6c-8.4,8.6-16.4,16.6-25.1,24.4c-0.2,0.2-0.4,0.3-0.5,0.5
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c-8.6,8.6-7,17-5.2,22.7c0.1,0.3,0.2,0.6,0.3,0.9c7.1,17.2,17.1,33.4,32.3,52.7l0.1,0.1c27.6,34,56.7,60.5,88.8,80.8
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c4.1,2.6,8.3,4.7,12.3,6.7c3.6,1.8,7,3.5,9.9,5.3c0.4,0.2,0.8,0.5,1.2,0.7c3.4,1.7,6.6,2.5,9.9,2.5c8.3,0,13.5-5.2,15.2-6.9
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    l34.2-34.2c3.4-3.4,8.8-7.5,15.1-7.5c6.2,0,11.3,3.9,14.4,7.3c0.1,0.1,0.1,0.1,0.2,0.2l55.1,55.1
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    C420.456,377.706,420.456,388.206,410.256,398.806z" />
                                    <path
                                        d="M256.056,112.706c26.2,4.4,50,16.8,69,35.8s31.3,42.8,35.8,69c1.1,6.6,6.8,11.2,13.3,11.2c0.8,0,1.5-0.1,2.3-0.2
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c7.4-1.2,12.3-8.2,11.1-15.6c-5.4-31.7-20.4-60.6-43.3-83.5s-51.8-37.9-83.5-43.3c-7.4-1.2-14.3,3.7-15.6,11
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    S248.656,111.506,256.056,112.706z" />
                                    <path
                                        d="M473.256,209.006c-8.9-52.2-33.5-99.7-71.3-137.5s-85.3-62.4-137.5-71.3c-7.3-1.3-14.2,3.7-15.5,11
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c-1.2,7.4,3.7,14.3,11.1,15.6c46.6,7.9,89.1,30,122.9,63.7c33.8,33.8,55.8,76.3,63.7,122.9c1.1,6.6,6.8,11.2,13.3,11.2
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    c0.8,0,1.5-0.1,2.3-0.2C469.556,223.306,474.556,216.306,473.256,209.006z" />
                                </g>
                            </g>
                        </svg>
                    </div>
                </div>

                <label for="address" class="mt-4 mb-2 block text-sm font-medium">Address</label>
                <div class="relative">
                    <input type="text" id="address" name="address"
                        class="w-full rounded-md border border-gray-200 px-4 py-3 pl-11 text-sm uppercase shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Enter your street address" />
                    <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 1024 1024" fill="#000000" class="icon"
                            version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M512 1012.8c-253.6 0-511.2-54.4-511.2-158.4 0-92.8 198.4-131.2 283.2-143.2h3.2c12 0 22.4 8.8 24 20.8 0.8 6.4-0.8 12.8-4.8 17.6-4 4.8-9.6 8.8-16 9.6-176.8 25.6-242.4 72-242.4 96 0 44.8 180.8 110.4 463.2 110.4s463.2-65.6 463.2-110.4c0-24-66.4-70.4-244.8-96-6.4-0.8-12-4-16-9.6-4-4.8-5.6-11.2-4.8-17.6 1.6-12 12-20.8 24-20.8h3.2c85.6 12 285.6 50.4 285.6 143.2 0.8 103.2-256 158.4-509.6 158.4z m-16.8-169.6c-12-11.2-288.8-272.8-288.8-529.6 0-168 136.8-304.8 304.8-304.8S816 145.6 816 313.6c0 249.6-276.8 517.6-288.8 528.8l-16 16-16-15.2zM512 56.8c-141.6 0-256.8 115.2-256.8 256.8 0 200.8 196 416 256.8 477.6 61.6-63.2 257.6-282.4 257.6-477.6C768.8 172.8 653.6 56.8 512 56.8z m0 392.8c-80 0-144.8-64.8-144.8-144.8S432 160 512 160c80 0 144.8 64.8 144.8 144.8 0 80-64.8 144.8-144.8 144.8zM512 208c-53.6 0-96.8 43.2-96.8 96.8S458.4 401.6 512 401.6c53.6 0 96.8-43.2 96.8-96.8S564.8 208 512 208z"
                                fill="" />
                        </svg>
                    </div>
                </div>


                <p class="mt-8 text-lg font-medium">Shipping Methods</p>
                <ul class="grid w-full gap-6 md:grid-cols-2 mt-5">
                    <li>
                        <input type="radio" id="inside-dhaka" name="shipping_class" value="inside-dhaka"
                            class="hidden peer" required />
                        <label for="inside-dhaka"
                            class="peer-checked:border-2 peer-checked:border-gray-700 peer-checked:bg-base-300 bg-white flex cursor-pointer select-none rounded-lg border border-gray-300 p-4">
                            <div class="block">
                                <div class="w-full text-lg font-semibold">Inside Dhaka</div>
                                <div class="w-full">1-2 Days</div>
                            </div>
                        </label>
                    </li>
                    <li>
                        <input type="radio" id="outside-dhaka" name="shipping_class" value="outside-dhaka"
                            class="hidden peer">
                        <label for="outside-dhaka"
                            class="peer-checked:border-2 peer-checked:border-gray-700 peer-checked:bg-base-300 bg-white flex cursor-pointer select-none rounded-lg border border-gray-300 p-4">
                            <div class="block">
                                <div class="w-full text-lg font-semibold">Outside Dhaka</div>
                                <div class="w-full">2-3 Days</div>
                            </div>
                        </label>
                    </li>
                </ul>
                <p class="mt-8 text-lg font-medium">Payment Methods</p>
                <ul class="grid w-full gap-6 md:grid-cols-2 mt-5">
                    <li>
                        <input type="radio" id="cash-on-delivery" name="payment_provider" value="cash-on-delivery"
                            class="hidden peer" required />
                        <label for="cash-on-delivery"
                            class="peer-checked:border-2 peer-checked:border-gray-700 peer-checked:bg-base-300 bg-white flex cursor-pointer select-none rounded-lg border border-gray-300 p-4">
                            <div class="block">
                                <div class="w-full text-lg font-semibold">Cash on Delivery</div>
                            </div>
                        </label>
                    </li>
                    <li>
                        <input type="radio" id="sslcommerz" name="payment_provider" value="sslcommerz"
                            class="hidden peer">
                        <label for="sslcommerz"
                            class="peer-checked:border-2 peer-checked:border-gray-700 peer-checked:bg-base-300 bg-white flex cursor-pointer select-none rounded-lg border border-gray-300 p-4">
                            <div class="block">
                                <div class="w-full text-lg font-semibold">Online Payment</div>
                            </div>
                        </label>
                    </li>
                </ul>


                <!-- Total -->
                <div class="mt-6 border-t border-b py-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">Subtotal</p>
                        <p class="font-semibold text-gray-900">৳ {{ $product->sale_price }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">Shipping</p>
                        <p class="font-semibold text-gray-900">৳ {{ $product->sale_price }}</p>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">Total</p>
                    <p class="text-2xl font-semibold text-gray-900">৳ {{ $product->sale_price }}</p>
                </div>
            </div>
            <button class="mt-4 mb-8 w-full rounded-md bg-gray-900 px-6 py-3 font-medium text-white">Place Order</button>
            <img src="{{ asset('checkout.png') }}" class="mx-auto h-16" />
        </div>
    </div>
@endsection
