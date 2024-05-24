<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShippingClass: string implements HasLabel, HasColor
{
    case InsideDhaka = 'Inside Dhaka';
    case OutsideDhaka = 'Outside Dhaka';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::InsideDhaka => 'primary',
            self::OutsideDhaka => 'warning',
        };
    }
}
