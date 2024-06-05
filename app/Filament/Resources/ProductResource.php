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

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->columnSpan(['md' => 1]),
            Forms\Components\TextInput::make('slug')
                ->regex('/^[a-z-]+$/i')
                ->required()
                ->maxLength(255)
                ->columnSpan(['md' => 1]),
            SelectTree::make('category_id')
                ->label('Category')
                ->relationship('category', 'name', 'parent_id')
                ->nullable()
                ->columnSpan(['md' => 1]),
            Forms\Components\TextInput::make('sale_price')
                ->gt('production_cost')
                ->prefix('৳')
                ->required()
                ->numeric()
                ->columnSpan(['md' => 1]),
            Forms\Components\TextInput::make('production_cost')
                ->lt('sale_price')
                ->prefix('৳')
                ->required()
                ->numeric()
                ->columnSpan(['md' => 1]),
            Forms\Components\Select::make('stock_status')
                ->options(StockStatus::class)
                ->required()
                ->reactive()
                ->columnSpan(['md' => 1]),
            Forms\Components\TextInput::make('stock')
                ->numeric()
                ->hidden(fn($get) => $get('stock_status') !== 'In Stock')
                ->required(fn($get) => $get('stock_status') === 'In Stock')
                ->columnSpan(['md' => 1]),
            Forms\Components\Textarea::make('description')
                ->required()
                ->maxLength(5000)
                ->columnSpan(['md' => 2]),
            Forms\Components\KeyValue::make('meta')
                ->nullable()
                ->columnSpan(['md' => 2]),
            Forms\Components\KeyValue::make('production_cost_breakdown')
                ->keyLabel('Title')
                ->valueLabel('Price')
                ->required()
                ->columnSpan(['md' => 2]),
            Forms\Components\FileUpload::make('main_photo')
                ->image()
                ->maxSize(1024)
                ->imageEditor()
                ->disk('public')
                ->visibility('public')
                ->required()
                ->columnSpan(['md' => 2]),
            Forms\Components\FileUpload::make('photos')
                ->image()
                ->maxSize(1024)
                ->maxFiles(5)
                ->imageEditor()
                ->reorderable()
                ->openable()
                ->downloadable()
                ->multiple()
                ->disk('public')
                ->visibility('public')
                ->columnSpan(['md' => 2]),
            Forms\Components\TextInput::make('video_link')
                ->url()
                ->placeholder('YouTube link is acceptable')
                ->nullable()
                ->columnSpanFull()
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_photo')
                    ->disk('public')
                    ->circular(),
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
