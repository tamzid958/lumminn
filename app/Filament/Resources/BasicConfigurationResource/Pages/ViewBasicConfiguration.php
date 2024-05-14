<?php

namespace App\Filament\Resources\BasicConfigurationResource\Pages;

use App\Filament\Resources\BasicConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBasicConfiguration extends ViewRecord
{
    protected static string $resource = BasicConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
