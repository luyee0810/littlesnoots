<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class AdoptionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id', 'user_id', 'applicant_name', 'applicant_email', 'applicant_phone',
        'message', 'home_type', 'has_other_pets', 'status', 'staff_notes',
        'reviewed_at', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'has_other_pets' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    // ---- Relationships -------------------------------------------------

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ---- Scopes --------------------------------------------------------

    /** Still needs someone to do something about it. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'reviewing']);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    // ---- State machine -------------------------------------------------
    //
    //                    ┌──> approved
    // pending ──> reviewing ──> rejected
    //    │  └──────────────────> approved | rejected
    //    └──> withdrawn (by the applicant)

    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'pending' => ['reviewing', 'approved', 'rejected', 'withdrawn'],
        'reviewing' => ['approved', 'rejected', 'withdrawn'],
    ];

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /** @param  array<string, mixed>  $attributes */
    protected function transitionTo(string $status, array $attributes = []): bool
    {
        if (! $this->canTransitionTo($status)) {
            throw new RuntimeException(
                "Cannot move application {$this->id} from {$this->status} to {$status}."
            );
        }

        return $this->update([...$attributes, 'status' => $status]);
    }

    /** Picked up, but no decision yet — tells the applicant they're being considered. */
    public function markReviewing(User $reviewer, ?string $notes = null): bool
    {
        return $this->transitionTo('reviewing', [
            'staff_notes' => $notes ?? $this->staff_notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    public function markApproved(User $reviewer, ?string $notes = null): bool
    {
        return $this->transitionTo('approved', [
            'staff_notes' => $notes ?? $this->staff_notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    public function markRejected(User $reviewer, ?string $notes = null): bool
    {
        return $this->transitionTo('rejected', [
            'staff_notes' => $notes ?? $this->staff_notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    /** The applicant pulling out — no reviewer involved. */
    public function markWithdrawn(): bool
    {
        return $this->transitionTo('withdrawn');
    }

    // ---- Helpers -------------------------------------------------------

    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'reviewing'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'New',
            'reviewing' => 'Being considered',
            'approved' => 'Approved',
            'rejected' => 'Not chosen',
            'withdrawn' => 'Withdrawn',
        };
    }
}
