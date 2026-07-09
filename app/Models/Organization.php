<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'type', 'email', 'phone', 'website',
        'address1', 'city', 'state', 'postcode', 'country',
        'mission_statement', 'adoption_policy', 'hours', 'facebook', 'instagram',
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'array',
        ];
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Single-line address for display. */
    public function fullAddress(): ?string
    {
        $parts = array_filter([$this->address1, $this->city, $this->state, $this->postcode]);

        return $parts ? implode(', ', $parts) : null;
    }
}
