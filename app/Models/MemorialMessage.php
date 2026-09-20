<?php

namespace App\Models;

use App\Models\Concerns\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MemorialMessage extends Model
{
    use Reportable;

    protected $fillable = ['pet_memorial_id', 'user_id', 'body'];

    public function memorial(): BelongsTo
    {
        return $this->belongsTo(PetMemorial::class, 'pet_memorial_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---- Moderation ----------------------------------------------------

    public function reportSummary(): string
    {
        return Str::limit($this->body, 200);
    }

    public function reportUrl(): string
    {
        return $this->memorial
            ? route('memorials.show', $this->memorial).'#guestbook'
            : route('memorials.index');
    }

    /** The memorial's owner moderates their own tribute page; so does staff. */
    public function isDeletableBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->id === $this->user_id
            || $user->id === $this->memorial?->user_id
            || $user->isStaff();
    }
}
