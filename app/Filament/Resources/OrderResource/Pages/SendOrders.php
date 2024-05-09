<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SendOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.send-orders';
    use InteractsWithTable;

    protected function getHeaderWidgets(): array
    {
        return [
        ];
    }

    // create a view

    protected function getTableQuery(): ?Builder
    {
        return Order::query()->where('shipping_status', '=', 'On Hold');
    }


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
                    ->action(function (Collection $records) use ($table): void {
                    })
            ]);
    }
}
