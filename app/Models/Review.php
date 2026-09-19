<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'provider_profile_id', 'user_id', 'rating', 'body'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    // ---- Relationships -------------------------------------------------

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---- Helpers -------------------------------------------------------

    /** "Sarah L." — reviews are public, so the owner's surname is shortened. */
    public function reviewerName(): string
    {
        $parts = preg_split('/\s+/', trim($this->user->name));
        $first = array_shift($parts);

        return $parts === [] ? $first : $first.' '.mb_substr(end($parts), 0, 1).'.';
    }
}
