<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PetMemorial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'slug', 'pet_name', 'species', 'photo_path',
        'born_on', 'passed_on', 'tribute',
    ];

    protected function casts(): array
    {
        return [
            'born_on' => 'date',
            'passed_on' => 'date',
        ];
    }

    /** Give every memorial a unique, human-readable slug from the pet's name. */
    protected static function booted(): void
    {
        static::creating(function (PetMemorial $memorial) {
            if (! $memorial->slug) {
                $memorial->slug = Str::slug($memorial->pet_name).'-'.Str::lower(Str::random(6));
            }
        });
    }

    // ---- Relationships -------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candles(): HasMany
    {
        return $this->hasMany(MemorialCandle::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MemorialMessage::class)->latest();
    }

    // ---- Helpers -------------------------------------------------------

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * External URLs and root-relative paths (bundled demo images) are returned
     * as-is; uploaded files resolve from the public disk.
     */
    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }

        if (str_starts_with($this->photo_path, '/')) {
            return $this->photo_path;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    /** e.g. "2010 – 2024", or a single year, or null when no dates are known. */
    public function lifespanLabel(): ?string
    {
        $born = $this->born_on?->format('Y');
        $passed = $this->passed_on?->format('Y');

        return match (true) {
            $born && $passed => "{$born} – {$passed}",
            (bool) $passed => $passed,
            (bool) $born => $born,
            default => null,
        };
    }

    public function isCandledBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->candles->contains('user_id', $user->id);
    }
}
