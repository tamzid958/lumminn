<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('shipping_status', '=', 'On Hold')->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('send')
                ->label('Send Orders')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->url(OrderResource::getUrl('send')),
        ];
    }
}
