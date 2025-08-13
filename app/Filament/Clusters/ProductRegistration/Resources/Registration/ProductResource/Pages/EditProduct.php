<?php

namespace App\Filament\Clusters\ProductRegistration\Resources\Registration\ProductResource\Pages;

use App\Filament\Clusters\ProductRegistration\Resources\Registration\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
