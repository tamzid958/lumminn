<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;


class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make()->after(function (Product $record) {
                if ($record->main_photo) {
                    Storage::disk('public')->delete($record->main_photo);
                }
                if ($record->photos) {
                    foreach ($record->photos as $ph) Storage::disk('public')->delete($ph);
                }
            }),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['stock_status'] === 'Unlimited') $data['stock'] = null;
        return $data;
    }
}
