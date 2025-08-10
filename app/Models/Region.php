<?php

namespace App\Models;

use App\Models\Registration\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'wilayah',
        'currency',
        'kurs'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
