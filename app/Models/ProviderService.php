<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderService extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_profile_id', 'service_category_id', 'title', 'description',
        'price', 'price_unit', 'currency', 'additional_pet_price',
        'min_units', 'max_pets', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'additional_pet_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ---- Relationships -------------------------------------------------

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
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

    // ---- Helpers -------------------------------------------------------

    public function label(): string
    {
        return $this->title ?: $this->category->name;
    }

    public function priceLabel(): string
    {
        return 'RM '.number_format((float) $this->price, 0).' / '.$this->price_unit;
    }

    /**
     * Server-side price calculation — the only place a booking total is worked out.
     * The base price covers one pet; each extra pet adds `additional_pet_price` per unit.
     */
    public function totalFor(int $units, int $pets = 1): float
    {
        $units = max($units, 1);
        $extraPets = max($pets - 1, 0);
        $extra = $extraPets * (float) ($this->additional_pet_price ?? 0);

        return round($units * ((float) $this->price + $extra), 2);
    }
}
