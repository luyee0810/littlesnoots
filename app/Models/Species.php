<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    use HasFactory;

    /** Laravel would otherwise pluralise this to "specieses". */
    protected $table = 'species';

    protected $fillable = ['name', 'slug'];

    public function breeds(): HasMany
    {
        return $this->hasMany(Breed::class);
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
