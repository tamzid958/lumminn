<!doctype html>
<html>
<head>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
@if(!empty($packingReceipts))
    <div class="grid grid-cols-2 gap-2 p-2">
        @foreach($packingReceipts as $receipt)
            <div class="border-2 border-black border-solid rounded-md p-3">
                <div class="flex justify-between">
                    <div class="flex-1 overflow-hidden">
                        <ul>
                            <li class="text-gray-600 uppercase" style="font-size:14px">Customer</li>
                            <li style='font-size:12px flex-wrap'>{{ $receipt['name'] }}</li>
                            <li style='font-size:10px'>{{ $receipt['phone_number'] }}</li>
                            <li class="flex-wrap" style='font-size:10px'>{{ $receipt['address'] }}</li>
                        </ul>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <ul>
                            <li class="text-gray-600 uppercase" style="font-size:14px">Order # {{ $receipt['id'] }}</li>
                            <li style='font-size:12px'>Method: {{ $receipt['shipping_provider_name'] }}</li>
                            <li style='font-size:12px' class='font-bold'>Consgt: {{ $receipt['shipping_id'] }}</li>
                            <li style='font-size:12px' class="text-red-900">Due: {{ $receipt['due_amount'] }} BDT</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
</body>
</html>

