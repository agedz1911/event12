<?php

namespace App\Filament\Clusters\ProductRegistration\Resources\Registration;

use App\Filament\Clusters\ProductRegistration;
use App\Filament\Clusters\ProductRegistration\Resources\Registration\ProductResource\Pages;
use App\Filament\Clusters\ProductRegistration\Resources\Registration\ProductResource\RelationManagers;
use App\Models\Registration\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $cluster = ProductRegistration::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\Select::make('region_id')
                    ->relationship('region', 'currency')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('early_bird')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('date_start_early_bird')
                    ->required()
                    ->native(false),
                Forms\Components\DatePicker::make('date_end_early_bird')
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('normal_price')
                    ->numeric()
                    ->default(null),
                Forms\Components\DatePicker::make('date_start_normal_price')
                    ->native(false),
                Forms\Components\DatePicker::make('date_end_normal_price')
                    ->native(false),
                Forms\Components\TextInput::make('onsite_price')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('date_start_onsite_price')
                    ->required()
                    ->native(false),
                Forms\Components\DatePicker::make('date_end_onsite_price')
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('kuota')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region.wilayah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('early_bird')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_start_early_bird')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_end_early_bird')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('normal_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_start_normal_price')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_end_normal_price')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('onsite_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_start_onsite_price')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_end_onsite_price')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kuota')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
