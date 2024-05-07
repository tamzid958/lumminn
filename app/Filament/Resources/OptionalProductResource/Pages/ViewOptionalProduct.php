<?php

namespace App\Filament\Resources\OptionalProductResource\Pages;

use App\Filament\Resources\OptionalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOptionalProduct extends ViewRecord
{
    protected static string $resource = OptionalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
