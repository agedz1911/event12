<?php

namespace App\Filament\Exports\Registration;

use App\Models\Registration\Participant;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ParticipantExporter extends Exporter
{
    protected static ?string $model = Participant::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('user.name')
                ->label('Owner'),
            ExportColumn::make('id_participant'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('nik'),
            ExportColumn::make('participant_type'),
            ExportColumn::make('title_specialist'),
            ExportColumn::make('speciality'),
            ExportColumn::make('title'),
            ExportColumn::make('name_on_certificate'),
            ExportColumn::make('institution'),
            ExportColumn::make('email'),
            ExportColumn::make('phone_number'),
            ExportColumn::make('country'),
            ExportColumn::make('address'),
            ExportColumn::make('province'),
            ExportColumn::make('city'),
            ExportColumn::make('postal_code'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your participant export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
