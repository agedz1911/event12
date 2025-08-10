<?php

namespace App\Filament\Resources\Registration\ParticipantResource\Pages;

use App\Filament\Resources\Registration\ParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParticipant extends EditRecord
{
    protected static string $resource = ParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
