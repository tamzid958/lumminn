<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DiscountType: string implements HasLabel, HasColor
{
    case Flat = 'Flat';
    case Percentage = 'Percentage';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Flat => 'primary',
            self::Percentage => 'secondary',
        };
    }
}
