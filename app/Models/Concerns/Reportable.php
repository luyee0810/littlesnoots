<?php

namespace App\Models\Concerns;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** Shared by anything a reader can flag: memorial messages, reviews. */
trait Reportable
{
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function isReportedBy(?User $user): bool
    {
        return $user !== null && $this->reports()->where('user_id', $user->id)->exists();
    }

    /** Shown to moderators in the queue, so it has to work for every type. */
    abstract public function reportSummary(): string;

    /** Where a moderator goes to see the thing in context. */
    abstract public function reportUrl(): string;
}
