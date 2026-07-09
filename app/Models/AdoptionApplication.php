<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdoptionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id', 'user_id', 'applicant_name', 'applicant_email', 'applicant_phone',
        'message', 'home_type', 'has_other_pets', 'status', 'staff_notes', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'has_other_pets' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
