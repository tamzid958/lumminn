<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

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
