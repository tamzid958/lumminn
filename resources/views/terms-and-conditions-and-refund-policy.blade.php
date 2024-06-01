@extends('components.layouts.app', [
    'title' => 'Terms and Conditions | Refund Policy',
    'description' => 'Terms and Conditions | Refund Policy',
])

@section('content')
    <div class="container mx-auto p-8">
        <div class="rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold mb-4">Terms and Conditions</h1>
            <p class="mb-4">Welcome to {{ config('app.name') }}! These terms and conditions outline the rules and
                regulations
                for the use of {{ config('app.name') }}'s Website, located at {{ config('app.url') }}.</p>
            <p class="mb-4">By accessing this website we assume you accept these terms and conditions. Do not continue to
                use {{ config('app.name') }} if you do not agree to take all of the terms and conditions stated on this
                page.
            </p>

            <h2 class="text-2xl font-semibold mt-6 mb-4">License</h2>
            <p class="mb-4">Unless otherwise stated, {{ config('app.name') }} and/or its licensors own the intellectual
                property rights for all material on {{ config('app.name') }}. All intellectual property rights are reserved.
                You may access this from {{ config('app.name') }} for your own personal use subjected to restrictions set in
                these terms and conditions.
            </p>
            <h2 class="text-2xl font-semibold mt-6 mb-4">User Comments</h2>
            <p class="mb-4">Certain parts of this website offer the opportunity for users to post and exchange opinions and
                information in certain areas of the website. {{ config('app.name') }} does not filter, edit, publish or
                review
                Comments prior to their presence on the website. Comments do not reflect the views and opinions of
                {{ config('app.name') }}, its agents and/or affiliates. Comments reflect the views and opinions of the
                person
                who post their views and opinions. To the extent permitted by applicable laws, {{ config('app.name') }}
                shall
                not be liable for the Comments or for any liability, damages or expenses caused and/or suffered as a result
                of any use of and/or posting of and/or appearance of the Comments on this website.
            </p>
            <h2 class="text-2xl font-semibold mt-6 mb-4">Hyperlinking to our Content</h2>
            <p class="mb-4">The following organizations may link to our Website without prior written approval:</p>
            <ul class="list-disc list-inside mb-4">
                <li>Government agencies;</li>
                <li>Search engines;</li>
                <li>News organizations;</li>
                <li>Online directory distributors may link to our Website in the same manner as they hyperlink to the
                    Websites of other listed businesses; and</li>
                <li>System wide Accredited Businesses except soliciting non-profit organizations, charity shopping malls,
                    and charity fundraising groups which may not hyperlink to our Web site.</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-4">iFrames</h2>
            <p class="mb-4">Without prior approval and written permission, you may not create frames around our Webpages
                that alter in any way the visual presentation or appearance of our Website.</p>

            <h1 class="text-3xl font-bold mt-12 mb-4">Refund Policy</h1>
            <p class="mb-4">Thank you for shopping at {{ config('app.name') }}. If you are not entirely satisfied with
                your
                purchase, we're here to help.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-4">Returns</h2>
            <p class="mb-4">You have 12 hours to return an item from the date you received it. To be eligible for a
                return, your item must be unused and in the same condition that you received it. Your item must be in the
                original packaging. Your item needs to have the receipt or proof of purchase.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-4">Refunds</h2>
            <p class="mb-4">Once we receive your item, we will inspect it and notify you that we have received your
                returned item. We will immediately notify you on the status of your refund after inspecting the item. If
                your return is approved, we will initiate a refund to your credit card (or original method of payment). You
                will receive the credit within a certain amount of days, depending on your card issuer's policies.</p>
            <p class="mb-4 font-bold">Please note that power lenses and any lenses are non-refundable.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-4">Shipping</h2>
            <p class="mb-4">You will be responsible for paying for your own shipping costs for returning your item.
                Shipping costs are non-refundable. If you receive a refund, the cost of return shipping will be deducted
                from your refund.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-4">Contact Us</h2>
            <p class="mb-4">If you have any questions on how to return your item to us, contact us at support@lumminn.com.
            </p>
        </div>
    </div>
@endsection
