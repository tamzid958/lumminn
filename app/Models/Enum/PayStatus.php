<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasLabel;

enum PayStatus: string implements HasLabel
{
    case Pending = 'Pending';
    case Paid = 'Paid';
    case Refunded = 'Refunded';

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
