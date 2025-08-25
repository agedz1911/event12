<?php

namespace App\Models\Registration;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'reg_code',
        'participant_id',
        'subtotal', 
        'total',
        'discount',
        'coupon',
        'status'
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function items():HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'order_id');
    }
}
