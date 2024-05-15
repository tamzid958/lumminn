<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderItem;
use App\Providers\OrderServiceProvider;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
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

        $mandatoryOrderItems = DB::table('order_items')
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

        $data['products'] = $mandatoryOrderItems->map(fn($item) => (array)$item)->all();
        $data['optional_products'] = $optionalOrderItems->map(fn($item) => (array)$item)->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($data['shipping_status'] !== 'On Hold') {
            $record->update($data);
        } else {
            OrderItem::query()->where('order_id', $data['id'])->forceDelete();


            $mandatoryOrderItems = OrderServiceProvider::convertToOrderItems($data, 'products');

            $optionalOrderItems = isset($data['optional_products']) && count($data['optional_products']) > 0 ?
                OrderServiceProvider::convertToOrderItems($data, 'optional_products') : [];

            $orderItems = array_merge($mandatoryOrderItems, $optionalOrderItems);


            $data['total_amount'] = array_reduce($orderItems, function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0);

            $freeShipping = OrderServiceProvider::checkIfAnyFreeShippingProduct($data, "edit");

            $shipping_provider = DB::table('shipping_providers')->find($data['shipping_provider_id']);

            if (!$freeShipping) {
                $data['shipping_amount'] = match ($data['shipping_class']) {
                    'Inside Dhaka' => $shipping_provider->inside_dhaka_charge,
                    default => $shipping_provider->outside_dhaka_charge,
                };
            } else {
                $data['shipping_amount'] = 0;
            }

            $record->update([
                'total_amount' => $data['total_amount'],
                'additional_amount' => $data['additional_amount'],
                'discount_amount' => $data['discount_amount'],
                'shipping_amount' => $data['shipping_amount'],
                'advance_amount' => $data['advance_amount'],
                'pay_status' => $data['pay_status'],
                'shipping_status' => $data['shipping_status'],
                'shipping_class' => $data['shipping_class'],
                'payment_id' => $data['payment_id'],
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'address' => $data['address'],
                'shipping_provider_id' => $data['shipping_provider_id'],
                'payment_provider_id' => $data['payment_provider_id'],
                'note' => $data['note'],
                'attachment' => $data['attachment'],
            ]);

            $orderItems = collect($orderItems)->map(function ($orderItem) use ($data) {
                return [
                    'order_id' => $data['id'],
                    'product_id' => $orderItem['product_id'],
                    'optional_product_id' => $orderItem['optional_product_id'],
                    'quantity' => $orderItem['quantity'],
                    'price' => $orderItem['price'],
                    'production_cost' => $orderItem['production_cost'],
                ];
            })->toArray();

            DB::table('order_items')->insert($orderItems);
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
