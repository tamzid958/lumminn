<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: "bengali";
            src: url({{ public_path('fonts/bengali.ttf') }}) format("truetype");
        }

        body {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .font-size-14 {
            font-size: 14px;
        }

        .font-size-12 {
            font-size: 12px;
        }

        .font-size-10 {
            font-size: 10px;
        }

        .text-red-900 {
            color: #991b1b;
        }

        .font-bold {
            font-weight: bold;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid black;
            vertical-align: middle;
            /* Vertically center content */
        }
    </style>
    <title>Invoice Receipt {{ date('m/d/Y h:i:s a', time()) }}</title>
</head>

<body>
    @if (!empty($packingReceipts))
        <div>
            <h4 style="margin-bottom: 4px; font-weight: bold; text-align: center">Invoice
                Receipt {{ date('m/d/Y h:i:s a', time()) }}</h4>
            @foreach ($packingReceipts as $receipt)
                <table style="border-style: solid;margin-top:8px;margin-bottom: 8px; width: 100%;">
                    <tr>
                        <td style="width: 50%; ">
                            <p style="padding-left: 8px">
                                <span class="text-gray-600 uppercase font-size-14">Customer</span>
                                <br>
                                <span class="flex-wrap font-size-12"
                                    style="font-family: bengali">{{ $receipt['name'] }}</span>
                                <br>
                                <span class="font-size-10">{{ $receipt['phone_number'] }}</span>
                                <br>
                                <span class="flex-wrap font-size-10"
                                    style="font-family: bengali">{{ $receipt['address'] }}</span>
                            </p>
                        </td>
                        <td style="width: 50%;">
                            <p style="padding-left: 8px">
                                <span class="text-gray-600 uppercase font-size-14">Order #{{ $receipt['id'] }}</span>
                                <br>
                                <span class="font-size-12">Method: {{ $receipt['shipping_provider_name'] }}</span>
                                <br>
                                <span class='font-bold font-size-12'>Consgt: {{ $receipt['shipping_id'] }}</span>
                                <br>
                                <span class="text-red-900 font-size-12">Due: {{ $receipt['due_amount'] }} BDT</span>
                                <br>
                                <span class="font-size-10">{{ $receipt['order_items'] }}</span>
                            </p>
                        </td>
                    </tr>
                </table>
            @endforeach
        </div>
    @endif
</body>

</html>
