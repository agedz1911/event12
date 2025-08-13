<?php

namespace App\Filament\Clusters\ProductRegistration\Resources\Registration\ProductResource\Pages;

use App\Filament\Clusters\ProductRegistration\Resources\Registration\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
