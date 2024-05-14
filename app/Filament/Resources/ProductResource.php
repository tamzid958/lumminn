<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Enum\StockStatus;
use App\Models\Product;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mansoor\FilamentVersionable\Table\RevisionsAction;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->regex('/^[a-z-]+$/i')
                    ->required()
                    ->maxLength(255),
                SelectTree::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name', 'parent_id')
                    ->nullable(),
                Forms\Components\TextInput::make('sale_price')
                    ->gt('production_cost')
                    ->prefix('৳')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('production_cost')
                    ->lt('sale_price')
                    ->prefix('৳')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('stock_status')
                    ->options(StockStatus::class)
                    ->required()
                    ->reactive(),
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->hidden(fn($get) => $get('stock_status') === 'Unlimited'),
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
                    ->nullable(),
                Forms\Components\KeyValue::make('production_cost_breakdown')
                    ->columnSpan(2)
                    ->keyLabel('Title')
                    ->valueLabel('Price')
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
                    Forms\Components\TextInput::make('video_link')
                    ->url()
                    ->placeholder('YouTube link is acceptable')
                    ->nullable()
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
                Tables\Columns\TextColumn::make('sale_price')
                    ->prefix('৳')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                RevisionsAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'revisions' => Pages\ProductRevisions::route('/{record}/revisions'),
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
