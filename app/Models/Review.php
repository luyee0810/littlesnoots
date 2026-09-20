<?php

namespace App\Models;

use App\Models\Concerns\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory, Reportable;

    protected $fillable = [
        'booking_id', 'provider_profile_id', 'user_id', 'rating', 'body',
        'provider_reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'replied_at' => 'datetime',
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

    // ---- Moderation ----------------------------------------------------

    public function reportSummary(): string
    {
        return $this->rating.'★ — '.Str::limit($this->body ?: '(no comment)', 200);
    }

    public function reportUrl(): string
    {
        return $this->providerProfile
            ? route('providers.show', $this->providerProfile).'#reviews'
            : route('services.index');
    }

    /**
     * One reply per review. A sitter answering criticism publicly is fair;
     * editing the answer afterwards to change what a reader saw is not.
     */
    public function canBeRepliedToBy(?User $user): bool
    {
        return $user !== null
            && $this->provider_reply === null
            && $this->providerProfile?->user_id === $user->id;
    }

    public function reply(string $body): bool
    {
        return $this->update(['provider_reply' => $body, 'replied_at' => now()]);
    }
}
