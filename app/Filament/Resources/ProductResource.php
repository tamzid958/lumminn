<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Enum\StockStatus;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->nullable(),
                Forms\Components\TextInput::make('sale_price')
                    ->gt('production_cost')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('production_cost')
                    ->lt('sale_price')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('stock_status')
                    ->options(StockStatus::class),
                Forms\Components\TextInput::make('stock')
                    ->numeric(),
                Forms\Components\Checkbox::make('is_shipping_charge_applicable')
                    ->columnSpan(2)
                    ->default(true)
                    ->label('Apply Shipping Charge'),
                Forms\Components\RichEditor::make('description')
                    ->columnSpan(2)
                    ->required()
                    ->maxLength(255),
                Forms\Components\KeyValue::make('meta')
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\KeyValue::make('production_cost_breakdown')
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\FileUpload::make('main_photo')
                    ->image()
                    ->maxSize(1024)
                    ->imageEditor()
                    ->columnSpan(2)
                    ->disk('public')
                    ->visibility('public')
                    ->required(),
                Forms\Components\FileUpload::make('photos')
                    ->image()
                    ->maxSize(1024)
                    ->maxFiles(5)
                    ->imageEditor()
                    ->reorderable()
                    ->openable()
                    ->downloadable()
                    ->columnSpan(2)
                    ->multiple()
                    ->disk('public')
                    ->visibility('public'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('production_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
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
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
