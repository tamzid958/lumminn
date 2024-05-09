<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockStatus: string implements HasLabel, HasColor
{
    case InStock = 'In Stock';
    case OutOfStock = 'Out of Stock';
    case OnBackOrder = 'On backorder';
    case Unlimited = 'Unlimited';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::InStock, self::Unlimited => 'success',
            self::OutOfStock => 'danger',
            self::OnBackOrder => 'warning'
        };
    }
}
