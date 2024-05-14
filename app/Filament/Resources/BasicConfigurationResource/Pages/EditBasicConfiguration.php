<?php

namespace App\Filament\Resources\BasicConfigurationResource\Pages;

use App\Filament\Resources\BasicConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBasicConfiguration extends EditRecord
{
    protected static string $resource = BasicConfigurationResource::class;

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
