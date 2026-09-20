<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    /** What a reporter can pick, and how it reads in the queue. */
    public const REASONS = [
        'abusive' => 'Abusive or hurtful',
        'spam' => 'Spam or advertising',
        'off_topic' => 'Off topic',
        'false' => 'Untrue or misleading',
        'other' => 'Something else',
    ];

    protected $fillable = [
        'reportable_type', 'reportable_id', 'user_id',
        'reason', 'notes', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    // ---- Relationships -------------------------------------------------

    /** The flagged thing — a memorial message or a review. */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ---- Scopes --------------------------------------------------------

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    // ---- Workflow ------------------------------------------------------

    /** The content was removed — closes every other report on the same item. */
    public function markActioned(User $reviewer): void
    {
        static::where('reportable_type', $this->reportable_type)
            ->where('reportable_id', $this->reportable_id)
            ->open()
            ->update([
                'status' => 'actioned',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);
    }

    public function markDismissed(User $reviewer): void
    {
        $this->update([
            'status' => 'dismissed',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst($this->reason);
    }
}
