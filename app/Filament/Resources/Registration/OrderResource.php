<?php

namespace App\Filament\Resources\Registration;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RegStatus;
use App\Filament\Resources\Registration\OrderResource\Pages;
use App\Filament\Resources\Registration\OrderResource\RelationManagers;
use App\Models\Registration\Order;
use App\Models\Registration\Participant;
use App\Models\Registration\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Registration';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Registration Detail')
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('reg_code')
                                        ->label('Registration Code')
                                        ->default('REG-' . random_int(10000, 99999))
                                        ->disabled()
                                        ->dehydrated()
                                        ->required()
                                        ->maxLength(20)
                                        ->unique(Order::class, 'reg_code', ignoreRecord: true),
                                    Select::make('user_id')
                                        ->label('Participant')
                                        ->options(Participant::all()->mapWithKeys(function ($user) {
                                            return [$user->id => $user->id_participant . ' | ' . $user->name . ' ' . $user->last_name];
                                        }))
                                        ->searchable()
                                        ->required(),

                                    ToggleButtons::make('status')
                                        ->required()
                                        ->options(RegStatus::class)
                                        ->inline(true),
                                ])->columns(2)
                        ]),
                    Step::make('Product Registration')
                        ->schema([
                            Repeater::make('regItems')
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            Select::make('product_id')
                                                ->label('Product')
                                                ->options(Product::all()->mapWithKeys(function ($product) {
                                                    return [$product->id => $product->name .  ' | ' .  $product->early_bird .  ' | ' .  $product->normal_price . ' | ' . $product->onsite_price];
                                                }))
                                                ->reactive()
                                                ->searchable(),
                                            TextInput::make('quantity')
                                                ->label('Quantity')
                                                ->numeric()
                                                ->reactive()
                                                ->required(),
                                            TextInput::make('unit_price')
                                                ->label('Unit Price')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated()
                                                ->reactive(),
                                        ])->columns(3),
                                ])
                                ->addActionLabel('Add Product')
                                ->reorderable(false),
                        ]),
                    Step::make('Order Detail')
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('coupon')
                                        ->label('Coupon Code'),
                                    TextInput::make('discount')
                                        ->label('Discount Amount')
                                        ->numeric()
                                        ->reactive()
                                        ->afterStateUpdated(function ($livewire) {
                                            $livewire->calculateGrandTotal();
                                        }),
                                    TextInput::make('total')
                                        ->label('Total Amount')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(),
                                ])
                        ]),
                    Step::make('Payment')
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('Payment Amount')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(),
                                    ToggleButtons::make('payment_method')
                                        ->label('Payment Method')
                                        ->options(PaymentMethod::class)
                                        ->inline()
                                        ->required(),
                                    ToggleButtons::make('payment_status')
                                        ->label('Payment Status')
                                        ->options(PaymentStatus::class)
                                        ->inline()
                                        ->required(),
                                    DatePicker::make('payment_date')
                                        ->label('Payment Date'),
                                ])->columns(2),
                            FileUpload::make('attachment')
                                ->maxSize(3072)
                                ->downloadable()
                                ->reorderable()
                                ->panelLayout('grid')
                                ->image()
                                ->imageEditor()
                                ->storeFileNamesIn('attachment_file_names')
                                ->directory('Payment_Receipt'),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
