<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Jobs\CheckDeliveryStatusJob;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Action::make('dispatch')
                    ->label('Dispatch')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->url(OrderResource::getUrl('send')),
                Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(fn() => dispatch(new CheckDeliveryStatusJob()))
                    ->requiresConfirmation(),
            ],
            )->label('Manage orders')->button()->color('success')->visible(fn() => Gate::allows('update_order'))
        ];
    }
}
