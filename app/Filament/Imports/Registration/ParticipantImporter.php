<?php

namespace App\Filament\Imports\Registration;

use App\Models\Registration\Participant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ParticipantImporter extends Importer
{
    protected static ?string $model = Participant::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('id_participant')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('last_name')
                ->rules(['max:255']),
            ImportColumn::make('nik')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('participant_type')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('title_specialist')
                ->rules(['max:255']),
            ImportColumn::make('speciality')
                ->rules(['max:255']),
            ImportColumn::make('title')
                ->rules(['max:255']),
            ImportColumn::make('name_on_certificate')
                ->rules(['max:255']),
            ImportColumn::make('institution')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('phone_number')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('country')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('address'),
            ImportColumn::make('province')
                ->rules(['max:255']),
            ImportColumn::make('city')
                ->rules(['max:255']),
            ImportColumn::make('postal_code')
                ->numeric()
                ->rules(['integer']),
        ];
    }

    public function resolveRecord(): ?Participant
    {
        // return Participant::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Participant();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your participant import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
