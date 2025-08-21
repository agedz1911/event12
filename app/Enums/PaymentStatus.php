<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Paid = 'Paid' ;
    case Unpaid = 'Unpaid';
    

    public function getLabel(): string
    {
        return match($this) {
            self::Paid => 'Paid',
            self::Unpaid => 'Unpaid',
        };   
    }

    public function getColor(): string|array|null
    {
        return match($this){
            self::Paid => 'success',
            self::Unpaid => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match($this){
            self::Paid => 'heroicon-m-check-circle',
            self::Unpaid => 'heroicon-m-x-circle',
            
        };
    }
}