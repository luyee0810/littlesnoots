<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'tagline', 'description', 'icon',
        'pricing_unit', 'requires_date_range', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_date_range' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ---- Relationships -------------------------------------------------

    public function providerServices(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ---- Scopes --------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ---- Helpers -------------------------------------------------------

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** "per night", "per walk" — used wherever a price is shown. */
    public function priceSuffix(): string
    {
        return 'per '.$this->pricing_unit;
    }

    /** Plural unit for a quantity, e.g. 3 → "nights". */
    public function unitLabel(int $quantity = 1): string
    {
        return $quantity === 1 ? $this->pricing_unit : $this->pricing_unit.'s';
    }
}
