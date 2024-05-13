<div class="card w-96 h-72 glass bg-base-200">
    <figure><img class="bg-base-300 object-cover w-full" src="{{ asset('storage/' . $product->main_photo) }}"
            alt="{{ $product->name }}" />
    </figure>
    <div class="card-body">
        <h2 class="card-title">{{ $product->name }}</h2>
        <div class="card-actions justify-end">
            <a href="/products/{{ $product->slug }}"
                class="inline-flex items-center font-medium text-primary hover:underline">
                Buy now
                <svg class="w-4 h-4 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 14 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M1 5h12m0 0L9 1m4 4L9 9" />
                </svg>
            </a>
        </div>
    </div>
</div>
