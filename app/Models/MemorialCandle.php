<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorialCandle extends Model
{
    protected $fillable = ['pet_memorial_id', 'user_id'];

    public function memorial(): BelongsTo
    {
        return $this->belongsTo(PetMemorial::class, 'pet_memorial_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
