<?php

namespace App\Filament\Resources\BasicConfigurationResource\Pages;

use App\Filament\Resources\BasicConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBasicConfigurations extends ListRecords
{
    protected static string $resource = BasicConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
