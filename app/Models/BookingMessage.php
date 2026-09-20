<?php

namespace App\Models;

use App\Models\Concerns\Reportable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BookingMessage extends Model
{
    use Reportable;

    protected $fillable = ['booking_id', 'user_id', 'body', 'read_at'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Messages the given user has not read — i.e. everything the other side sent. */
    public function scopeUnreadFor(Builder $query, User $user): Builder
    {
        return $query->whereNull('read_at')->where('user_id', '!=', $user->id);
    }

    // ---- Moderation ----------------------------------------------------

    public function reportSummary(): string
    {
        return Str::limit($this->body, 200);
    }

    public function reportUrl(): string
    {
        return $this->booking
            ? route('bookings.show', $this->booking).'#messages'
            : route('home');
    }
}
