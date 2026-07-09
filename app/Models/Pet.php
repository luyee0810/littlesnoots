<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'species_id', 'breed_id', 'sex', 'size',
        'age_months', 'color', 'status', 'adoption_fee',
        'vaccinated', 'sterilized', 'good_with_kids', 'good_with_pets',
        'location', 'description', 'listed_by', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'adoption_fee' => 'decimal:2',
            'vaccinated' => 'boolean',
            'sterilized' => 'boolean',
            'good_with_kids' => 'boolean',
            'good_with_pets' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // ---- Relationships -------------------------------------------------

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PetPhoto::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    public function lister(): BelongsTo
    {
        return $this->belongsTo(User::class, 'listed_by');
    }

    // ---- Scopes --------------------------------------------------------

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    // ---- Helpers -------------------------------------------------------

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function primaryPhoto(): ?PetPhoto
    {
        return $this->photos->firstWhere('is_primary', true) ?? $this->photos->first();
    }

    public function ageForHumans(): ?string
    {
        if ($this->age_months === null) {
            return null;
        }

        if ($this->age_months < 12) {
            return $this->age_months.' mo';
        }

        $years = intdiv($this->age_months, 12);
        $months = $this->age_months % 12;

        return $months ? "{$years}y {$months}m" : "{$years}y";
    }
}
