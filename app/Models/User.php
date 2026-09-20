<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'suspended_at', 'suspension_reason', 'suspended_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ---- Relationships -------------------------------------------------

    /** Pet listings this user manages (shelter staff / owners). */
    public function listedPets(): HasMany
    {
        return $this->hasMany(Pet::class, 'listed_by');
    }

    public function adoptionApplications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    /** Service provider profile (Phase 2) — a user may be both an adopter and a provider. */
    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }

    /** Service bookings this user has made as a pet owner. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Pet memorials this user has created. */
    public function memorials(): HasMany
    {
        return $this->hasMany(PetMemorial::class);
    }

    // ---- Role helpers --------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'admin'], true);
    }

    /**
     * Provider status is not a `role` — an adopter can also be a sitter, which the
     * single-value role enum can't express. It's the existence of a live profile.
     */
    public function isProvider(): bool
    {
        return $this->providerProfile()->where('status', 'approved')->exists();
    }

    /** Has started the provider flow at all, live or not — drives dashboard links. */
    public function hasProviderProfile(): bool
    {
        return $this->providerProfile()->exists();
    }

    // ---- Suspension ----------------------------------------------------

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /** The account survives — their listings and bookings are still needed. */
    public function suspend(self $by, string $reason): void
    {
        $this->forceFill([
            'suspended_at' => now(),
            'suspension_reason' => $reason,
            'suspended_by' => $by->id,
        ])->save();
    }

    public function reinstate(): void
    {
        $this->forceFill([
            'suspended_at' => null,
            'suspension_reason' => null,
            'suspended_by' => null,
        ])->save();
    }

    /**
     * Who may act on this account. Staff can suspend ordinary members; only an
     * admin can touch another staff member, and nobody can suspend themselves
     * or the last way back into the site.
     */
    public function canBeModeratedBy(?self $actor): bool
    {
        if ($actor === null || $actor->id === $this->id || ! $actor->isStaff()) {
            return false;
        }

        return $this->isStaff() ? $actor->isAdmin() : true;
    }

    public function suspender(): BelongsTo
    {
        return $this->belongsTo(self::class, 'suspended_by');
    }
}
