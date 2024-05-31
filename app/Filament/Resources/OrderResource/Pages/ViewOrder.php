<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderItem;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $orderId = $data['id'];
        
        $mandatoryOrderItems = OrderItem::with('product')
            ->where('order_id', $orderId)
            ->whereNotNull('product_id')
            ->get(['quantity', 'product_id as id']);

        $optionalOrderItems = OrderItem::with('optionalProduct')
            ->where('order_id', $orderId)
            ->whereNotNull('optional_product_id')
            ->get(['quantity', 'optional_product_id as id']);

        $data['products'] = $mandatoryOrderItems->map(function ($item) {
            return [
                'quantity' => $item->quantity,
                'id' => $item->id,
            ];
        })->all();

        $data['optional_products'] = $optionalOrderItems->map(function ($item) {
            return [
                'quantity' => $item->quantity,
                'id' => $item->id,
            ];
        })->all();

        return $data;
    }
}
