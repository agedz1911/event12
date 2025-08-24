<?php

namespace App\Filament\Resources\Registration\OrderResource\Pages;

use App\Filament\Resources\Registration\OrderResource;
use App\Models\Registration\Order;
use App\Models\Registration\OrderItem;
use App\Models\Registration\Transaction;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('create')
            ->label('')
            ->hidden();
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('createAnother')
            ->label('')
            ->hidden();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan total dihitung dengan benar
        $subtotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['unit_price'])) {
                    $subtotal += $item['unit_price'];
                }
            }
        }

        $discount = $data['discount'] ?? 0;
        $data['total'] = $subtotal - $discount;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Create the main order
        $orderData = [
            'reg_code' => $data['reg_code'],
            'participant_id' => $data['participant_id'],
            'total' => $data['total'],
            'discount' => $data['discount'] ?? 0,
            'coupon' => $data['coupon'] ?? null,
            'status' => $data['status'],
        ];

        $order = Order::create($orderData);

        // Create order items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                if (isset($itemData['product_id']) && isset($itemData['quantity']) && isset($itemData['unit_price'])) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                    ]);
                }
            }
        }

        // Create transaction if payment data exists
        if (isset($data['payment_method']) || isset($data['payment_status'])) {
            Transaction::create([
                'order_id' => $order->id,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_date' => $data['payment_date'] ?? null,
                'payment_status' => $data['payment_status'] ?? null,
                'amount' => $data['total'],
                'attachment' => $data['attachment'] ?? null,
            ]);
        }

        return $order;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
