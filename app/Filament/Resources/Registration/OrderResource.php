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
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

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
                                    Select::make('participant_id')
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
                            Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            Select::make('product_id')
                                                ->label('Product')
                                                ->options(Product::all()->mapWithKeys(function ($product) {
                                                    return [$product->id => $product->name .  ' | ' .  $product->early_bird .  ' | ' .  $product->normal_price . ' | ' . $product->onsite_price];
                                                }))
                                                ->live()
                                                ->searchable()
                                                ->required()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                                    if ($state) {
                                                        $product = Product::find($state);
                                                        $quantity = $get('quantity') ?: 1;

                                                        if ($product) {
                                                            $price = static::getProductPrice($product);
                                                            $set('unit_price', $price * $quantity);

                                                            // Trigger recalculation
                                                            static::recalculateFromItems($livewire);
                                                        }
                                                    }
                                                }),
                                            TextInput::make('quantity')
                                                ->label('Quantity')
                                                ->numeric()
                                                ->default(1)
                                                ->live()
                                                ->required()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                                    $productId = $get('product_id');
                                                    if ($productId && $state) {
                                                        $product = Product::find($productId);
                                                        if ($product) {
                                                            $price = static::getProductPrice($product);
                                                            $set('unit_price', $price * $state);

                                                            // Trigger recalculation
                                                            static::recalculateFromItems($livewire);
                                                        }
                                                    }
                                                }),
                                            TextInput::make('unit_price')
                                                ->label('Unit Price')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated()
                                                ->live(),
                                        ])->columns(3),
                                ])
                                ->addActionLabel('Add Product')
                                ->reorderable(false)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                    static::recalculateFromItems($livewire);
                                })
                                ->deleteAction(
                                    fn(Action $action) => $action->after(function ($livewire) {
                                        static::recalculateFromItems($livewire);
                                    })
                                ),
                        ]),
                    Step::make('Order Detail')
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('subtotal')
                                        ->label('Subtotal')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated()
                                        ->default(0)
                                        ->prefix('Rp'),
                                    TextInput::make('coupon')
                                        ->label('Coupon Code')
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            static::applyCoupon($state, $set, $get);
                                        }),
                                    TextInput::make('discount')
                                        ->label('Discount Amount')
                                        ->numeric()
                                        ->default(0)
                                        ->live()
                                        ->prefix('Rp')
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            static::calculateGrandTotal($set, $get);
                                        }),
                                    TextInput::make('total')
                                        ->label('Total Amount')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated()
                                        ->default(0)
                                        ->prefix('Rp'),
                                ])
                        ]),
                    Step::make('Payment')
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('payment_amount')
                                        ->label('Payment Amount')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->prefix('Rp'),
                                    ToggleButtons::make('transaction.payment_method')
                                        ->label('Payment Method')
                                        ->options(PaymentMethod::class)
                                        ->inline()
                                        ->required(),
                                    ToggleButtons::make('transaction.payment_status')
                                        ->label('Payment Status')
                                        ->options(PaymentStatus::class)
                                        ->inline()
                                        ->required(),
                                    DatePicker::make('transaction.payment_date')
                                        ->label('Payment Date'),
                                    Hidden::make('transaction.amount')
                                        ->dehydrated()
                                        ->default(0),
                                ])->columns(2),
                            FileUpload::make('transaction.attachment')
                                ->maxSize(3072)
                                ->downloadable()
                                ->reorderable()
                                ->panelLayout('grid')
                                ->image()
                                ->imageEditor()
                                ->directory('Payment_Receipt'),
                        ]),
                ])->columnSpanFull()
                    ->submitAction(new HtmlString('<button type="submit">Submit</button>'))
            ])
            ->live();
    }

    protected static function getProductPrice(Product $product): float
    {
        if ($product->is_early_bird) {
            return (float) $product->early_bird;
        } elseif ($product->is_onsite) {
            return (float) $product->onsite_price;
        } else {
            return (float) $product->normal_price;
        }
    }

    protected static function recalculateFromItems($livewire): void
    {
        $items = $livewire->data['items'] ?? [];
        $subtotal = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (isset($item['unit_price']) && is_numeric($item['unit_price'])) {
                    // unit_price already contains the total for this line item (price * quantity)
                    $subtotal += (float) $item['unit_price'];
                }
            }
        }

        // Update subtotal
        $livewire->data['subtotal'] = $subtotal;

        // Calculate total with existing discount
        $discount = (float) ($livewire->data['discount'] ?? 0);
        $total = max(0, $subtotal - $discount); // Pastikan total tidak negatif

        // Update total and payment amount
        $livewire->data['total'] = $total;
        $livewire->data['payment_amount'] = $total;

        // Sync transaction amount
        if (!isset($livewire->data['transaction'])) {
            $livewire->data['transaction'] = [];
        }
        $livewire->data['transaction']['amount'] = $total;
    }


    protected static function applyCoupon($couponCode, callable $set, callable $get): void
    {
        $subtotal = (float) ($get('subtotal') ?: 0);
        $discount = 0;

        if (!empty($couponCode) && $subtotal > 0) {
            if ($couponCode === 'discount10') {
                $discount = $subtotal * 0.10; // 10% discount
            } elseif ($couponCode === 'discount20') {
                $discount = $subtotal * 0.20; // 20% discount
            }
        }

        $set('discount', $discount);
        static::calculateGrandTotal($set, $get);
    }


    protected static function calculateGrandTotal(callable $set, callable $get): void
    {
        $subtotal = (float) ($get('subtotal') ?: 0);
        $discount = (float) ($get('discount') ?: 0);
        $grandTotal = max(0, $subtotal - $discount); // Pastikan total tidak negatif

        $set('total', $grandTotal);
        $set('payment_amount', $grandTotal);
        $set('transaction.amount', $grandTotal);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reg_code')->label('Reg Code')->searchable()->sortable(),
                TextColumn::make('participant.last_name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),
                // ->getStateUsing(fn($record) => $record->first_name . ' ' . $record->last_name),
                TextColumn::make('items.product.name')
                    ->label('Products')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn($record) => $record->items->pluck('product.name')->join(', ')),
                TextColumn::make('items.quantity')
                    ->label('Quantity'),
                TextColumn::make('items.unit_price')
                    ->label('Unit Price'),
                TextColumn::make('total')->label('Total')->money('idr', true)->sortable(),
                TextColumn::make('discount')->label('Discount')->money('idr', true)->sortable(),
                TextColumn::make('coupon')->label('Coupon')->sortable(),
                TextColumn::make('transaction.payment_method')
                    ->label('Payment Method'),
                TextColumn::make('transaction.payment_date')
                    ->label('Payment Date'),
                TextColumn::make('transaction.payment_status')
                    ->label('Payment Status'),
                TextColumn::make('transaction.amount')
                    ->label('Payment Amount'),
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
