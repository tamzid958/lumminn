<?php

namespace App\Filament\Resources;

use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Enum\PayStatus;
use App\Models\Enum\ShippingClass;
use App\Models\Enum\ShippingStatus;
use App\Models\OptionalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentProvider;
use App\Models\Product;
use App\Models\ShippingProvider;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use libphonenumber\PhoneNumberType;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use ValentinMorice\FilamentJsonColumn\FilamentJsonColumn;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('shipping_status', '=', 'On Hold')->count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric(),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn($record) => $record->phone_number)
                    ->searchable(),
                Tables\Columns\TextColumn::make('ipAddress.ip')
                    ->label("IP Address")
                    ->state(fn($record) => isset($record->ipAddress) ? ($record->ipAddress->alias ?? $record->ipAddress->ip) . " (" . $record->ipAddress->count . ")" : "")
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('geo_location')
                    ->wrap()
                    ->alignCenter()
                    ->copyable()
                    ->label('GEO Location')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pay_amount')
                    ->numeric()
                    ->prefix('৳'),
                Tables\Columns\TextColumn::make('payment_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('paymentProvider.name')
                    ->numeric(),
                Tables\Columns\TextColumn::make('pay_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('shippingProvider.name')
                    ->numeric(),
                Tables\Columns\TextColumn::make('shipping_class')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shipping_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->options(PayStatus::class),
                Tables\Filters\TernaryFilter::make('is_confirmed')
                    ->label('Confirmation'),
                DateRangeFilter::make('created_at')
                    ->timePicker()
                    ->autoApply()
                    ->withIndicator()
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->visible(fn() => Gate::allows('update_order'))
                    ->label(fn($record) => $record->is_confirmed ? "Confirmed" : "Confirm Order")
                    ->color(fn($record) => $record->is_confirmed ? "success" : "warning")
                    ->icon(fn($record) => $record->is_confirmed ? 'heroicon-o-check-badge' : 'heroicon-o-phone')
                    ->slideOver()
                    ->modalSubmitActionLabel('Confirm')
                    ->modalIconColor('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Confirm the order by calling the customer')
                    ->fillForm(function (Order $record) {
                        $orderId = $record['id'];

                        $mandatoryOrderItems = OrderItem::with('product')
                            ->where('order_id', $orderId)
                            ->whereNotNull('product_id')
                            ->get(['quantity', 'product_id as id']);

                        $shippingStatusCounts = Order::select('shipping_status', DB::raw('count(*) as total'))
                            ->where('phone_number', $record['phone_number'])
                            ->groupBy('shipping_status')
                            ->get()
                            ->toArray();

                        $shippingStatusString = '';

                        foreach ($shippingStatusCounts as $item) {
                            $status = strval($item['shipping_status']); // Cast the enum value to a string
                            $count = $item['total'];
                            $shippingStatusString .= ucfirst($status) . ": $count, ";
                        }

                        // Remove the trailing comma and space
                        $shippingStatusString = rtrim($shippingStatusString, ', ');

                        return [
                            'name' => $record['name'],
                            'phone_number' => $record['phone_number'],
                            'address' => $record['address'],
                            'shipping_class' => $record['shipping_class'],
                            'products' => $mandatoryOrderItems->map(function ($item) {
                                return [
                                    'quantity' => $item->quantity,
                                    'id' => $item->id,
                                ];
                            })->all(),
                            'previous_history' => $shippingStatusString,
                        ];
                    })
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        PhoneInput::make('phone_number')
                            ->onlyCountries(['bd'])
                            ->defaultCountry('bd')
                            ->validateFor(
                                country: 'bd',
                                type: PhoneNumberType::MOBILE,
                                lenient: true
                            )
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('shipping_class')
                            ->label("Class")
                            ->options(ShippingClass::class)
                            ->columnSpanFull(),
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
                            ->required(),
                        Forms\Components\Textarea::make('previous_history')
                            ->columnSpanFull()
                    ])
                    ->disabledForm()
                    ->disabled(fn($record): ?bool => $record['is_confirmed'])
                    ->action(function (Order $record): void {
                        $record['is_confirmed'] = true;
                        $record->save();
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
            ])
            ->bulkActions(actions: [
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make("confirm-selected")
                        ->visible(fn() => Gate::allows('update_order'))
                        ->label("Confirm selected")
                        ->color('warning')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->is_confirmed = true;
                                $record->save();
                            }
                        })->requiresConfirmation(),
                ]),
                ExportBulkAction::make()->exporter(OrderExporter::class)->chunkSize(500),
                Tables\Actions\BulkAction::make('send')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->label('Download Invoice')
                    ->action(function (Collection $records) {
                        $pdf = LaravelMpdf::loadView('components.download-invoice', [
                            'packingReceipts' => collect($records->toArray())->map(function ($record) {
                                $orderItems = OrderItem::with(['product', 'optionalProduct'])
                                    ->where('order_id', $record['id'])
                                    ->get();

                                $productsString = '';

                                foreach ($orderItems as $item) {
                                    $productName = $item->product ? $item->product->name : $item->optionalProduct->title;
                                    $quantity = $item->quantity;
                                    $itemString = "$productName ($quantity)";

                                    $productsString .= ($productsString ? ', ' : '') . $itemString;
                                }

                                return [
                                    'id' => $record['id'],
                                    'name' => $record['name'],
                                    'phone_number' => $record['phone_number'],
                                    'address' => $record['address'],
                                    'shipping_id' => $record['shipping_id'],
                                    'shipping_provider_name' => ShippingProvider::find($record['shipping_provider_id'])->name,
                                    'due_amount' => $record['pay_amount'],
                                    'order_items' => $productsString,
                                ];
                            })
                        ]);

                        $pdfContent = $pdf->output();

                        return response()->streamDownload(function () use ($pdfContent) {
                            echo $pdfContent;
                        }, "Invoice.pdf", ['Content-Type' => 'application/pdf']);
                    })->requiresConfirmation()
            ])
            ->defaultSort(function (Builder $query) {
                $query->orderByRaw(
                    "CASE
                            WHEN shipping_status = 'On Hold' AND is_confirmed = 0 THEN 1
                            WHEN shipping_status = 'On Hold' AND is_confirmed = 1 THEN 2
                            WHEN shipping_status = 'Cancelled' THEN 3
                            WHEN shipping_status = 'Packed' THEN 4
                            WHEN shipping_status = 'Dispatched' THEN 5
                            WHEN shipping_status = 'Completed' THEN 6
                            WHEN shipping_status = 'Returned' THEN 7
                            ELSE 8
                        END ASC");
            })
            ->deferLoading();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Forms\Components\Hidden::make('id')->disabledOn("create"),
                    Fieldset::make(
                        'Customer Information',
                    )->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord)
                            ->columnSpan(1),
                        PhoneInput::make('phone_number')
                            ->onlyCountries(['bd'])
                            ->defaultCountry('bd')
                            ->validateFor(
                                country: 'bd',
                                type: PhoneNumberType::MOBILE,
                                lenient: true
                            )
                            ->required()
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord)
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord)
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
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
                                    ->required()
                                    ->searchable()
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
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
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
                                    ->searchable()
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
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
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
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
                        Forms\Components\Select::make('shipping_class')
                            ->label("Class")
                            ->options(ShippingClass::class)
                            ->required()
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
                        Forms\Components\Select::make('shipping_status')
                            ->label("Status")
                            ->options(ShippingStatus::class)
                            ->default('On Hold')
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
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord)
                            ->required(),
                        Forms\Components\Select::make('pay_status')
                            ->label("Status")
                            ->options(PayStatus::class)
                            ->default('Pending')
                            ->required(),
                        Forms\Components\TextInput::make('payment_id')
                            ->label('Identifier')
                            ->placeholder('will be generated if empty')
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
                        FilamentJsonColumn::make('gateway_response')
                            ->viewerOnly()
                            ->editorHeight(200)
                            ->columnSpanFull()
                            ->disabled()
                    ])->columns(3),

                    Fieldset::make(
                        'Billing',
                    )->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->prefix('৳')
                            ->readOnly()
                            ->placeholder('will be generated')
                            ->numeric(),
                        Forms\Components\TextInput::make('shipping_amount')
                            ->prefix('৳')
                            ->readOnly()
                            ->placeholder('will be generated')
                            ->numeric(),
                        Forms\Components\TextInput::make('additional_amount')
                            ->prefix('৳')
                            ->numeric()
                            ->default(0)
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
                        Forms\Components\TextInput::make('discount_amount')
                            ->prefix('৳')
                            ->numeric()
                            ->default(0)
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
                        Forms\Components\TextInput::make('advance_amount')
                            ->prefix('৳')
                            ->numeric()
                            ->default(0)
                            ->disabled(fn(Get $get, Page $livewire): ?bool => $get('shipping_status') !== 'On Hold' &&
                                $livewire instanceof EditRecord),
                        Forms\Components\TextInput::make('pay_amount')
                            ->prefix('৳')
                            ->readOnly()
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
                ]
            );
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
