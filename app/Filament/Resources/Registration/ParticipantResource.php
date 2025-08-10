<?php

namespace App\Filament\Resources\Registration;

use App\Filament\Exports\Registration\ParticipantExporter;
use App\Filament\Imports\Registration\ParticipantImporter;
use App\Filament\Resources\Registration\ParticipantResource\Pages;
use App\Filament\Resources\Registration\ParticipantResource\RelationManagers;
use App\Models\Registration\Participant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;
    protected static ?string $navigationGroup = 'Registration';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        $countries = countries();

        return $form
            ->schema([

                Section::make()
                    ->schema([
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nik')
                            ->maxLength(16)
                            ->minLength(10)
                            ->numeric(),
                        Select::make('title')
                            ->native(false)
                            ->options([
                                'Prof' => 'Prof',
                                'MD' => 'MD',
                                'Mr' => 'Mr',
                                'Mrs' => 'Mrs',
                                'Ms' => 'Ms',
                            ]),
                        TextInput::make('title_specialist')
                            ->placeholder('SpU, SpBP, SpBS')
                            ->maxLength(255),
                        Select::make('speciality')
                            ->required()
                            ->native(false)
                            ->options([
                                'Specialist' => 'Specialist',
                                'Resident' => 'Resident',
                                'General Practitioner' => 'General Practitioner',
                                'Medical Student' => 'Medical Student',
                            ]),
                        TextInput::make('name_on_certificate')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('institution')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->tel()
                            ->required()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                    ])->columns(2),
                Section::make()
                    ->schema([
                        Select::make('country')
                            ->required()
                            ->searchable()
                            ->options(collect($countries)->mapWithKeys(function ($country) {
                                return [$country['name'] => $country['name']];
                            })->all()),
                        TextInput::make('province')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->numeric(),
                        Textarea::make('address')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make()
                    ->schema([
                        Select::make('participant_type')
                            ->required()
                            ->options([
                                'Participant' => 'Participant',
                                'Faculty' => 'Faculty',
                                'Committee' => 'Committee',
                                'Moderator' => 'Moderator',
                                'Instructor' => 'Instructor',
                            ])
                            ->default('Participant')
                            ->searchable()
                            ->multiple()
                            ->native(false),
                        TextInput::make('id_participant')
                            ->default(fn() => 'EVENT-' . random_int(10000, 99999))
                            ->required()
                            ->readOnly()
                            ->unique(Participant::class, 'id_participant', ignoreRecord: true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(ParticipantExporter::class),
                ImportAction::make()
                    ->importer(ParticipantImporter::class),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id_participant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->getStateUsing(fn($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('nik')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title_specialist')
                    ->searchable(),
                Tables\Columns\TextColumn::make('speciality')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_on_certificate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('institution')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ParticipantExporter::class)
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
