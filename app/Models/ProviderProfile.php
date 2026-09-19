<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'slug', 'headline', 'bio',
        'address1', 'city', 'state', 'postcode', 'country',
        'latitude', 'longitude', 'service_radius_km',
        'years_experience', 'home_type', 'has_fenced_yard', 'has_own_pets',
        'is_smoke_free', 'has_insurance',
        'accepts_species', 'accepts_sizes', 'max_pets_per_booking', 'available_days',
        'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'has_fenced_yard' => 'boolean',
            'has_own_pets' => 'boolean',
            'is_smoke_free' => 'boolean',
            'has_insurance' => 'boolean',
            'accepts_species' => 'array',
            'accepts_sizes' => 'array',
            'available_days' => 'array',
            'rating_avg' => 'decimal:2',
            'published_at' => 'datetime',
            'verified_email_at' => 'datetime',
            'verified_phone_at' => 'datetime',
            'verified_id_at' => 'datetime',
            'background_check_at' => 'datetime',
        ];
    }

    // ---- Relationships -------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProviderPhoto::class)->orderBy('sort_order');
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(ProviderUnavailableDate::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ---- Scopes --------------------------------------------------------

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** Providers offering a given category, by slug. */
    public function scopeInCategory(Builder $query, string $slug): Builder
    {
        return $query->whereHas('services', fn ($s) => $s->active()
            ->whereHas('category', fn ($c) => $c->where('slug', $slug)));
    }

    /** Providers offering any of the given categories, by slug. */
    public function scopeInCategories(Builder $query, array $slugs): Builder
    {
        return $query->whereHas('services', fn ($s) => $s->active()
            ->whereHas('category', fn ($c) => $c->whereIn('slug', $slugs)));
    }

    /** City or postcode match — Phase 2 keeps location search textual. */
    public function scopeInLocation(Builder $query, string $term): Builder
    {
        $like = '%'.$term.'%';

        return $query->where(fn ($q) => $q->where('city', 'like', $like)
            ->orWhere('postcode', 'like', $like));
    }

    /** Free-text over the provider's own words and the services they list. */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.$term.'%';

        return $query->where(function ($q) use ($like) {
            $q->where('headline', 'like', $like)
                ->orWhere('bio', 'like', $like)
                ->orWhereHas('services', fn ($s) => $s->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like))
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like));
        });
    }

    // ---- Helpers -------------------------------------------------------

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function primaryPhoto(): ?ProviderPhoto
    {
        return $this->photos->firstWhere('is_primary', true) ?? $this->photos->first();
    }

    /**
     * The photo showing a given service, so a category listing leads with the
     * right picture. Falls back to the primary photo.
     */
    public function photoFor(?ServiceCategory $category): ?ProviderPhoto
    {
        if (! $category) {
            return $this->primaryPhoto();
        }

        return $this->photos->firstWhere('service_category_id', $category->id)
            ?? $this->primaryPhoto();
    }

    /** Recompute the denormalised rating_avg / reviews_count from the reviews table. */
    public function refreshRating(): void
    {
        $stats = $this->reviews()->selectRaw('count(*) as total, avg(rating) as average')->first();

        $this->forceFill([
            'reviews_count' => (int) $stats->total,
            'rating_avg' => round((float) $stats->average, 2),
        ])->save();
    }

    public function isLive(): bool
    {
        return $this->status === 'approved'
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function locationLabel(): string
    {
        return collect([$this->city, $this->state])->filter()->implode(', ');
    }

    /** Cheapest active service, for the "from RM x" line on a result card. */
    public function cheapestService(): ?ProviderService
    {
        return $this->services->where('is_active', true)->sortBy('price')->first();
    }

    public function offersCategory(int $categoryId): bool
    {
        return $this->services->contains(
            fn (ProviderService $s) => $s->service_category_id === $categoryId && $s->is_active
        );
    }

    /** True when the provider works that weekday and hasn't blocked the date out. */
    public function isAvailableOn(\DateTimeInterface $date): bool
    {
        $days = $this->available_days;

        if (is_array($days) && $days !== []
            && ! in_array(strtolower($date->format('D')), array_map('strtolower', $days), true)) {
            return false;
        }

        return ! $this->unavailableDates()
            ->whereDate('date', $date->format('Y-m-d'))
            ->exists();
    }
}
