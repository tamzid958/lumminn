<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Providers\OrderServiceProvider;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $orderId = $data['id'];

        $mandatatoryOrderItems = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('order_items.quantity', 'order_items.product_id as id')
            ->where('order_items.order_id', $orderId)
            ->whereNotNull('order_items.product_id')
            ->get();

        $optionalOrderItems = DB::table('order_items')
            ->join('optional_products', 'order_items.optional_product_id', '=', 'optional_products.id')
            ->select('order_items.quantity', 'order_items.optional_product_id as id')
            ->where('order_items.order_id', $orderId)
            ->whereNotNull('order_items.optional_product_id')
            ->get();

        $data['products'] = $mandatatoryOrderItems->map(fn($item) => (array)$item)->all();
        $data['optional_products'] = $optionalOrderItems->map(fn($item) => (array)$item)->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $freeShipping = OrderServiceProvider::checkIfanyFreeShippingProduct($data, "edit");

        $shipping_provider = DB::table('shipping_providers')->find($data['shipping_provider_id']);

        if (!$freeShipping) {
            $data['shipping_amount'] = match ($data['shipping_class']) {
                'Inside Dhaka' => $shipping_provider->inside_dhaka_charge,
                default => $shipping_provider->outside_dhaka_charge,
            };
        } else {
            $data['shipping_amount'] = 0;
        }

        $data['pay_amount'] = $data['total_amount'] + $data['shipping_amount'] + $data['additional_amount'];

        return $data;
    }
}
