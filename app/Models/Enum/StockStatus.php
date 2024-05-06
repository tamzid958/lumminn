<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasLabel;

enum StockStatus: string implements HasLabel
{
    case InStock = 'InStock';
    case OutOfStock = 'OutOfStock';
    case OnBackOrder = 'OnBackorder';

    public function getLabel(): ?string
    {
        return $this->name;

        return match ($this) {
            self::InStock => 'In stock',
            self::OutOfStock => 'Out of stock',
            self::OnBackOrder => 'On backorder',
        };
    }
}
