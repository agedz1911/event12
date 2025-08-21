<?php

namespace App\Filament\Resources\Registration\OrderResource\Pages;

use App\Filament\Resources\Registration\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
