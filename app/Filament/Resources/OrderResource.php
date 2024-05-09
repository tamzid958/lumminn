<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Enum\PayStatus;
use App\Models\Enum\ShippingClass;
use App\Models\Enum\ShippingStatus;
use App\Models\OptionalProduct;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Product;
use App\Models\ShippingProvider;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Novadaemon\FilamentPrettyJson\PrettyJson;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(
                    'Customer Information',
                )->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\TextInput::make('phone_number')
                        ->tel()
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\Textarea::make('address')
                        ->required()
                        ->columnSpan(2)
                        ->maxLength(255)
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                ]),
                Fieldset::make(
                    'Order Items',
                )->schema([
                    Forms\Components\Repeater::make('products')
                        ->label('Mandatory')
                        ->minItems(1)
                        ->schema([
                            Forms\Components\Select::make('id')
                                ->label('Product')
                                ->options(function (callable $get) {
                                    $product = Product::all();
                                    return $product->pluck('name', 'id');
                                })
                                ->reactive()
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->minValue(1)
                                ->columnSpan(1)
                        ])
                        ->columns()
                        ->reorderable(false)
                        ->columnSpan(2)
                        ->required()
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),

                    Forms\Components\Repeater::make('optional_products')
                        ->label('Optional')
                        ->defaultItems(0)
                        ->schema([
                            Forms\Components\Select::make('id')
                                ->label('Product')
                                ->options(function (callable $get) {
                                    $product = OptionalProduct::all();
                                    return $product->pluck('title', 'id');
                                })
                                ->reactive()
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->minValue(1)
                                ->columnSpan(1)
                        ])
                        ->columns()
                        ->reorderable(false)
                        ->columnSpan(2)
                        ->nullable()
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold')
                ]),

                Fieldset::make(
                    'Shipping',
                )->schema([
                    Forms\Components\Select::make('shipping_provider_id')
                        ->label("Provider")
                        ->options(function (callable $get) {
                            $product = ShippingProvider::all();
                            return $product->pluck('name', 'id');
                        })
                        ->required()
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\Select::make('shipping_class')
                        ->label("Class")
                        ->options(ShippingClass::class)
                        ->required()
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\Select::make('shipping_status')
                        ->label("Status")
                        ->options(ShippingStatus::class)
                        ->default('OnHold')
                        ->required(),
                    Forms\Components\TextInput::make('shipping_id')
                        ->label('Identifier')
                        ->placeholder('will be generated')
                        ->disabledOn("create")
                ]),

                Fieldset::make(
                    'Payment',
                )->schema([
                    Forms\Components\Select::make('payment_provider_id')
                        ->label("Provider")
                        ->options(function (callable $get) {
                            $product = PaymentProvider::all();
                            return $product->pluck('name', 'id');
                        })
                        ->required(),
                    Forms\Components\Select::make('pay_status')
                        ->label("Status")
                        ->options(PayStatus::class)
                        ->default('Pending')
                        ->required(),
                    Forms\Components\TextInput::make('payment_id')
                        ->label('Identifier')
                        ->placeholder('will be generated')
                        ->disabledOn("create"),
                    PrettyJson::make('gateway_response')
                        ->columnSpanFull()
                ])->columns(3),

                Fieldset::make(
                    'Billing',
                )->schema([
                    Forms\Components\TextInput::make('total_amount')
                        ->prefix('৳')
                        ->disabled()
                        ->placeholder('will be generated')
                        ->numeric()
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\TextInput::make('shipping_amount')
                        ->prefix('৳')
                        ->disabled()
                        ->placeholder('will be generated')
                        ->numeric()
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\TextInput::make('additional_amount')
                        ->prefix('৳')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\TextInput::make('pay_amount')
                        ->prefix('৳')
                        ->disabled()
                        ->placeholder('will be generated')
                        ->numeric()
                        ->disabled(fn(Get $get): ?bool => $get('shipping_status') !== 'On Hold'),
                    Forms\Components\TextInput::make('transaction_amount')
                        ->prefix('৳')
                        ->disabled()
                        ->placeholder('will be generated')
                        ->numeric(),
                ]),
                Forms\Components\KeyValue::make('note')
                    ->keyLabel('Title')
                    ->valueLabel('Comment')
                    ->columnSpan(2),
                Forms\Components\FileUpload::make('attachment')
                    ->maxSize(1024)
                    ->maxFiles(5)
                    ->reorderable()
                    ->openable()
                    ->downloadable()
                    ->columnSpan(2)
                    ->multiple()
                    ->disk('public')
                    ->visibility('private')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('shipping_status')
                    ->options(ShippingStatus::class),
                Tables\Filters\SelectFilter::make('pay_status')
                    ->options(PayStatus::class)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                ExportBulkAction::make()
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'send' => Pages\SendOrders::route('/send'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
