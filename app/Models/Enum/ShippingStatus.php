<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasLabel;

enum ShippingStatus: string implements HasLabel
{
    case OnHold = 'OnHold';
    case Dispatched = 'Dispatched';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';
    case Returned = 'Returned';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OnHold => 'On Hold',
            self::Dispatched => 'Dispatched',
            self:: Completed => 'Completed',
            self:: Cancelled => 'Cancelled',
            self:: Returned => 'Returned'
        };
    }
}
