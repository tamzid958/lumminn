<?php

namespace App\Filament\Resources\OptionalProductResource\Pages;

use App\Filament\Resources\OptionalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOptionalProduct extends EditRecord
{
    protected static string $resource = OptionalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
