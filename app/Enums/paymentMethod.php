<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case BankTransfer = 'Bank Transfer' ;
    case CreditCard = 'Credit Card';
    

    public function getLabel(): string
    {
        return match($this) {
            self::BankTransfer => 'Bank Transfer',
            self::CreditCard => 'Credit Card',
        };   
    }

    public function getColor(): string|array|null
    {
        return match($this){
            self::BankTransfer => 'info',
            self::CreditCard => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match($this){
            self::BankTransfer => 'heroicon-m-banknotes',
            self::CreditCard => 'heroicon-m-credit-card',
            
        };
    }
}