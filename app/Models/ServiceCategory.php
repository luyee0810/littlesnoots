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

    /**
     * Lucide icon name for the category, matching the line-icon style used on
     * the homepage. Keyed on slug so it survives regardless of the stored emoji.
     */
    public function lucideIcon(): string
    {
        return match ($this->slug) {
            'boarding' => 'house',
            'house-sitting' => 'sofa',
            'dog-walking' => 'dog',
            'daycare' => 'sun',
            'grooming' => 'scissors',
            'pet-taxi' => 'car',
            'training' => 'graduation-cap',
            default => 'paw-print',
        };
    }

    /**
     * Modifier class that tints the category icon a distinct colour, so each
     * service reads as its own thing in the grid. Keyed on slug.
     */
    public function iconColorClass(): string
    {
        return match ($this->slug) {
            'boarding' => 'tile__icon--boarding',
            'house-sitting' => 'tile__icon--house-sitting',
            'dog-walking' => 'tile__icon--dog-walking',
            'daycare' => 'tile__icon--daycare',
            'grooming' => 'tile__icon--grooming',
            'pet-taxi' => 'tile__icon--pet-taxi',
            'training' => 'tile__icon--training',
            default => '',
        };
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
