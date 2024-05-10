<x-filament-panels::page>
    @if(!empty($packingReceipts))
        @php $counter = 0; @endphp

        @foreach($packingReceipts as $receipt)
            @if($counter % 4 == 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    @endif

                    <div
                        style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width: calc(25% - 20px); margin-right: 20px; padding: 10px; border: 1px solid #000; border-radius: 5px;">
                        <h2 style="margin-top: 0; color: #000; font-size: 16px;">Customer Information</h2>
                        <p style="margin: 3px 0; font-size: 14px;"><strong>Name:</strong> {{ $receipt['name'] }}</p>
                        <p style="margin: 3px 0; font-size: 14px;"><strong>Phone:</strong> {{ $receipt['phone'] }}</p>
                        <p style="margin: 3px 0; font-size: 14px;"><strong>Address:</strong> {{ $receipt['address'] }}
                        </p>

                        <h2 style="margin-top: 10px; color: #000; font-size: 16px;">Order Details</h2>
                        <p style="margin: 3px 0; font-size: 14px;"><strong>Order ID:</strong> {{ $receipt['id'] }}</p>
                        <p style="margin: 3px 0; font-size: 14px;"><strong>Consignment
                                ID:</strong> {{ $receipt['shipping_id'] }}</p>
                        <p style="margin: 3px 0; font-size: 14px;"><strong>Due Bill:</strong> {{ $receipt['due_bill'] }}
                        </p>
                    </div>

                    @php $counter++; @endphp

                    @if($counter % 4 == 0 || $loop->last)
                </div>
            @endif
        @endforeach
    @endif
</x-filament-panels::page>
