<?php

namespace App\Filament\Resources\Registration\OrderResource\Pages;

use App\Filament\Resources\Registration\OrderResource;
use App\Models\Registration\OrderItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('save')
            ->label('')
            ->hidden();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load transaction data
        if ($this->record->transaction) {
            $data['payment_method'] = $this->record->transaction->payment_method;
            $data['payment_date'] = $this->record->transaction->payment_date;
            $data['payment_status'] = $this->record->transaction->payment_status;
            $data['payment_amount'] = $this->record->transaction->amount;
            $data['attachment'] = $this->record->transaction->attachment;
        }

        // Calculate subtotal from items
        $subtotal = $this->record->items->sum('unit_price');
        $data['subtotal'] = $subtotal;
        $data['payment_amount'] = $data['total'];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update the main order
        $record->update([
            'reg_code' => $data['reg_code'],
            'participant_id' => $data['participant_id'],
            'total' => $data['total'],
            'discount' => $data['discount'] ?? 0,
            'coupon' => $data['coupon'] ?? null,
            'status' => $data['status'],
        ]);

        // Update order items
        $record->items()->delete(); // Delete existing items

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                if (isset($itemData['product_id']) && isset($itemData['quantity']) && isset($itemData['unit_price'])) {
                    OrderItem::create([
                        'order_id' => $record->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                    ]);
                }
            }
        }

        // Update or create transaction
        if (isset($data['payment_method']) || isset($data['payment_status'])) {
            $record->transaction()->updateOrCreate(
                ['order_id' => $record->id],
                [
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_date' => $data['payment_date'] ?? null,
                    'payment_status' => $data['payment_status'] ?? null,
                    'amount' => $data['total'],
                    'attachment' => $data['attachment'] ?? null,
                ]
            );
        }

        return $record;
    }
}
