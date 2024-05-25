<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Jobs\SendOrdersJob;
use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Gate;

class SendOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.send-orders';

    use InteractsWithTable;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dispatchOrders')
                    ->label('Dispatch Orders')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function() {
                        dispatch(new SendOrdersJob(auth()->user()));
                        Notification::make()
                            ->title('Request sent successfully')
                            ->body('Please check notification after a while.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::allows('update_order')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->where('shipping_status', '=', 'On Hold')->where('is_confirmed', '=', true))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(30)
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pay_amount')
                    ->numeric()
                    ->prefix('৳'),
                Tables\Columns\TextColumn::make('paymentProvider.name')
                    ->numeric(),
                Tables\Columns\TextColumn::make('pay_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('shippingProvider.name')
                    ->numeric(),
                Tables\Columns\TextColumn::make('shipping_status')
                    ->badge(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
            ])->defaultSort('created_at', 'desc');
    }

    // create a view

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getTableQuery(): ?Builder
    {
        return Order::query()->where('shipping_status', '=', 'On Hold')->where('is_confirmed', '=', true);
    }
}
