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
        // Load relasi transaction untuk form
        $record = $this->getRecord();
        
        if ($record->transaction) {
            $data['transaction'] = $record->transaction->toArray();
        }
        
        // Hitung subtotal dari items
        $subtotal = 0;
        foreach ($record->items as $item) {
            $subtotal += $item->unit_price;
        }
        $data['subtotal'] = $subtotal;
        $data['payment_amount'] = $record->total;
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hitung subtotal dari items
        $subtotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['unit_price'])) {
                    $subtotal += (float) $item['unit_price'];
                }
            }
        }
        
        $data['subtotal'] = $subtotal;
        
        // Hitung total dengan discount
        $discount = (float) ($data['discount'] ?? 0);
        $total = max(0, $subtotal - $discount); // Pastikan total tidak negatif
        $data['total'] = $total;
        
        // Sync transaction amount
        if (isset($data['transaction'])) {
            $data['transaction']['amount'] = $total;
        }
        
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Pisahkan data transaction
        $transactionData = $data['transaction'] ?? [];
        unset($data['transaction']);
        
        // Update order
        $record->update($data);
        
        // Update atau buat transaction
        if (!empty($transactionData)) {
            if ($record->transaction) {
                $record->transaction->update($transactionData);
            } else {
                $transactionData['order_id'] = $record->id;
                $record->transaction()->create($transactionData);
            }
        }
        
        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
