<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use RuntimeException;

class Booking extends Model
{
    use HasFactory;

    /** How long a provider has to respond before the request lapses. */
    public const RESPONSE_WINDOW_HOURS = 48;

    protected $fillable = [
        'reference', 'provider_profile_id', 'provider_service_id', 'service_category_id', 'user_id',
        'starts_at', 'ends_at', 'unit_quantity', 'unit_label',
        'pet_name', 'pet_species_id', 'pet_breed', 'pet_size', 'pet_count', 'pet_notes',
        'owner_name', 'owner_email', 'owner_phone', 'service_address', 'service_city',
        'unit_price', 'additional_pet_price', 'total', 'currency',
        'status', 'message', 'provider_response',
        'responded_at', 'cancelled_at', 'completed_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'unit_price' => 'decimal:2',
            'additional_pet_price' => 'decimal:2',
            'total' => 'decimal:2',
            'responded_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->reference ??= static::generateReference();
            $booking->expires_at ??= now()->addHours(self::RESPONSE_WINDOW_HOURS);
        });
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'TFC-'.strtoupper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    // ---- Relationships -------------------------------------------------

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ProviderService::class, 'provider_service_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function petSpecies(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'pet_species_id');
    }

    // ---- Scopes --------------------------------------------------------

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'accepted', 'in_progress']);
    }

    public function scopeForProvider(Builder $query, ProviderProfile $profile): Builder
    {
        return $query->where('provider_profile_id', $profile->id);
    }

    // ---- State machine -------------------------------------------------
    //
    //                  ┌──> declined
    // pending ─────────┼──> expired
    //    │             └──> cancelled_by_owner
    //    ↓ provider accepts
    // accepted ──> in_progress ──> completed
    //    └──> cancelled_by_owner | cancelled_by_provider

    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'pending' => ['accepted', 'declined', 'expired', 'cancelled_by_owner'],
        'accepted' => ['in_progress', 'completed', 'cancelled_by_owner', 'cancelled_by_provider'],
        'in_progress' => ['completed', 'cancelled_by_provider'],
    ];

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /** @param  array<string, mixed>  $attributes */
    protected function transitionTo(string $status, array $attributes = []): bool
    {
        if (! $this->canTransitionTo($status)) {
            throw new RuntimeException("Cannot move booking {$this->reference} from {$this->status} to {$status}.");
        }

        return $this->update([...$attributes, 'status' => $status]);
    }

    public function markAccepted(?string $response = null): bool
    {
        return $this->transitionTo('accepted', [
            'provider_response' => $response,
            'responded_at' => now(),
        ]);
    }

    public function markDeclined(?string $response = null): bool
    {
        return $this->transitionTo('declined', [
            'provider_response' => $response,
            'responded_at' => now(),
        ]);
    }

    public function markInProgress(): bool
    {
        return $this->transitionTo('in_progress');
    }

    public function markCompleted(): bool
    {
        return $this->transitionTo('completed', ['completed_at' => now()]);
    }

    public function markCancelledByOwner(): bool
    {
        return $this->transitionTo('cancelled_by_owner', ['cancelled_at' => now()]);
    }

    public function markCancelledByProvider(?string $response = null): bool
    {
        return $this->transitionTo('cancelled_by_provider', [
            'provider_response' => $response,
            'cancelled_at' => now(),
        ]);
    }

    public function markExpired(): bool
    {
        return $this->transitionTo('expired');
    }

    // ---- Helpers -------------------------------------------------------

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function isAwaitingResponse(): bool
    {
        return $this->status === 'pending';
    }

    public function hasLapsed(): bool
    {
        return $this->isAwaitingResponse()
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Awaiting response',
            'accepted' => 'Confirmed',
            'declined' => 'Declined',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'cancelled_by_owner' => 'Cancelled by you',
            'cancelled_by_provider' => 'Cancelled by provider',
            'expired' => 'Expired — no response',
            default => ucfirst($this->status),
        };
    }

    /** Design-system `.status` modifier for the status pill, keyed off the same vocabulary. */
    public function statusClasses(): string
    {
        return match ($this->status) {
            'pending' => 'status--warn',
            'accepted', 'in_progress' => 'status--ok',
            'completed' => 'status--idle',
            default => 'status--bad',
        };
    }

    public function dateRangeLabel(): string
    {
        if ($this->ends_at === null) {
            return $this->starts_at->format('D, j M Y · g:ia');
        }

        return $this->starts_at->format('j M').' – '.$this->ends_at->format('j M Y');
    }

    public function totalLabel(): string
    {
        return 'RM '.number_format((float) $this->total, 2);
    }
}
