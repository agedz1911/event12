<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RegStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'New';
    case Processing = 'Processing';
    case Validated = 'Validated';
    case Cancelled = 'Cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Processing => 'Processing',
            self::Validated => 'Validated',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::New => 'info',
            self::Processing => 'warning',
            self::Validated => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Processing => 'heroicon-m-arrow-path',
            self::Validated => 'heroicon-m-check-circle',
            self::Cancelled => 'heroicon-m-x-circle',
        };
    }
}
