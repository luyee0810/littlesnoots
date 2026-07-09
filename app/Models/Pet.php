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
        'name', 'slug', 'organization_id', 'species_id',
        'breed_id', 'secondary_breed_id', 'breed_mixed', 'breed_unknown',
        'age_group', 'age_months', 'gender', 'size', 'coat', 'color', 'secondary_color',
        'status', 'adoption_fee',
        'spayed_neutered', 'shots_current', 'house_trained', 'declawed', 'special_needs',
        'good_with_children', 'good_with_dogs', 'good_with_cats',
        'tags', 'location', 'description', 'listed_by',
        'published_at', 'status_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'adoption_fee' => 'decimal:2',
            'breed_mixed' => 'boolean',
            'breed_unknown' => 'boolean',
            'spayed_neutered' => 'boolean',
            'shots_current' => 'boolean',
            'house_trained' => 'boolean',
            'declawed' => 'boolean',
            'special_needs' => 'boolean',
            'good_with_children' => 'boolean',
            'good_with_dogs' => 'boolean',
            'good_with_cats' => 'boolean',
            'tags' => 'array',
            'published_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    // ---- Relationships -------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    public function secondaryBreed(): BelongsTo
    {
        return $this->belongsTo(Breed::class, 'secondary_breed_id');
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

    /** Human breed line, e.g. "Labrador Retriever & Poodle Mix". */
    public function breedLabel(): string
    {
        if ($this->breed_unknown) {
            return 'Mixed Breed';
        }

        $label = $this->breed?->name ?? $this->species->name;

        if ($this->secondaryBreed) {
            $label .= ' & '.$this->secondaryBreed->name;
        }

        if ($this->breed_mixed) {
            $label .= ' Mix';
        }

        return $label;
    }

    public function colorLabel(): ?string
    {
        return collect([$this->color, $this->secondary_color])->filter()->implode(' / ') ?: null;
    }

    public function ageForHumans(): ?string
    {
        if ($this->age_months !== null) {
            if ($this->age_months < 12) {
                return $this->age_months.' mo';
            }
            $years = intdiv($this->age_months, 12);
            $months = $this->age_months % 12;

            return $months ? "{$years}y {$months}m" : "{$years}y";
        }

        return $this->age_group ? ucfirst($this->age_group) : null;
    }
}
