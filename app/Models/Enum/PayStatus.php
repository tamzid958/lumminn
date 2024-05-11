<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PayStatus: string implements HasLabel, HasColor
{
    case Pending = 'Pending';
    case Paid = 'Paid';
    case Cancelled = 'Cancelled';
    case Refunded = 'Refunded';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'primary',
            self::Paid => 'success',
            self::Refunded => 'warning',
            self::Cancelled => 'danger'
        };
    }
}
