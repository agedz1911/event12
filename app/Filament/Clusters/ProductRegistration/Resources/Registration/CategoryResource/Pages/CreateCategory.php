<?php

namespace App\Filament\Clusters\ProductRegistration\Resources\Registration\CategoryResource\Pages;

use App\Filament\Clusters\ProductRegistration\Resources\Registration\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
