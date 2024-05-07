<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasLabel;

enum ShippingClass: string implements HasLabel
{
    case InsideDhaka = 'InsideDhaka';
    case OutsideDhaka = 'OutsideDhaka';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::InsideDhaka => 'Inside Dhaka',
            self::OutsideDhaka => 'Outside Dhaka',
        };
    }
}
