<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShippingStatus: string implements HasLabel, HasColor
{
    case OnHold = 'On Hold';
    case ReadyForDispatch = 'Ready for Dispatch';
    case Dispatched = 'Dispatched';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';
    case Returned = 'Returned';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OnHold => 'primary',
            self::Dispatched, self::ReadyForDispatch => 'warning',
            self::Completed => 'success',
            self::Cancelled, self::Returned => 'danger',
        };
    }
}
