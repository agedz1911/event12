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
        // Debug: lihat data yang masuk
        // dd($data); // uncomment untuk debug

        // Pastikan subtotal dihitung dari items (unit_price sudah berisi total per item)
        $subtotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['unit_price']) && is_numeric($item['unit_price'])) {
                    $subtotal += (float) $item['unit_price'];
                }
            }
        }

        // Set subtotal
        $data['subtotal'] = $subtotal;

        // Hitung total dengan discount
        $discount = (float) ($data['discount'] ?? 0);
        $total = max(0, $subtotal - $discount); // Pastikan total tidak negatif
        $data['total'] = $total;

        // Pastikan transaction amount sama dengan total
        if (isset($data['transaction'])) {
            $data['transaction']['amount'] = $total;
        }

        return $data;
    }


    protected function handleRecordCreation(array $data): Model
    {
        // Pisahkan data transaction dan items dari data order
        $transactionData = $data['transaction'] ?? [];
        $itemsData = $data['items'] ?? [];

        // Hapus dari data order
        unset($data['transaction'], $data['items']);

        // Buat order terlebih dahulu
        $order = static::getModel()::create($data);

        // Buat items jika ada
        if (!empty($itemsData) && is_array($itemsData)) {
            foreach ($itemsData as $itemData) {
                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);
            }
        }

        // Buat transaction jika ada data transaction
        if (!empty($transactionData)) {
            $order->transaction()->create([
                'payment_method' => $transactionData['payment_method'] ?? null,
                'payment_status' => $transactionData['payment_status'] ?? null,
                'payment_date' => $transactionData['payment_date'] ?? null,
                'amount' => $transactionData['amount'] ?? $order->total,
                'attachment' => $transactionData['attachment'] ?? null,
            ]);
        }

        return $order;
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
