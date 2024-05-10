<?php

namespace App\Models\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PayStatus: string implements HasLabel, HasColor, \JsonSerializable
{
    case Pending = 'Pending';
    case Paid = 'Paid';
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
        };
    }
    public function getType(): string
    {
        return match ($this) {
            PayStatus::Pending => 'Pending',
            PayStatus::Paid => 'Paid',
            PayStatus::Refunded => 'Refunded'
        };
    }

    public function jsonSerialize(): mixed
    {
        return $this->getType();
    }
}
