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

class SendOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.send-orders';

    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->where('shipping_status', '=', 'On Hold'))
            ->columns([
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
                Tables\Actions\BulkAction::make('send')->label('Send to Courier')
                    ->color('danger')
                    ->action(function (Collection $records): void {
                        dispatch(new SendOrdersJob($records->toArray(), auth()->user()));
                        Notification::make()
                            ->title('Request sent successfully')
                            ->body('Please check notification after a while.')
                            ->success()
                            ->send();
                        redirect('admin/orders/');
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
            ])->defaultSort('created_at', 'desc');
    }

    // create a view

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getTableQuery(): ?Builder
    {
        return Order::query()->where('shipping_status', '=', 'On Hold');
    }
}
