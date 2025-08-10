<?php

namespace App\Filament\Clusters\ProductRegistration\Resources\Registration\CategoryResource\Pages;

use App\Filament\Clusters\ProductRegistration\Resources\Registration\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
