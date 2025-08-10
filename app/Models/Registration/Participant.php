<?php

namespace App\Models\Registration;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_participant',
        'user_id',
        'first_name',
        'email',
        'country',
        'phone_number',
        'participant_type',
        'last_name',
        'nik',
        'title',
        'title_specialist',
        'speciality',
        'name_on_certificate',
        'institution',
        'address',
        'province',
        'city',
        'postal_code',
    ];

    protected $casts = [
        'participant_type' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
