<?php

namespace App\Filament\Clusters\ProductRegistration\Resources\RegionResource\Pages;

use App\Filament\Clusters\ProductRegistration\Resources\RegionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegions extends ListRecords
{
    protected static string $resource = RegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
