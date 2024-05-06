<?php

namespace App\Filament\Resources\PaymentProviderResource\Pages;

use App\Filament\Resources\PaymentProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentProvider extends ViewRecord
{
    protected static string $resource = PaymentProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
