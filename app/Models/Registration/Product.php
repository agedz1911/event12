<?php

namespace App\Models\Registration;

use App\Models\Region;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'category_id',
        'region_id',
        'name',
        'early_bird',
        'date_start_early_bird',
        'date_end_early_bird',
        'normal_price',
        'date_start_normal_price',
        'date_end_normal_price',
        'onsite_price',
        'date_start_onsite_price',
        'date_end_onsite_price',
        'kuota',
        'is_active'
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
