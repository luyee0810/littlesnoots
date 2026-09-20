<?php

namespace App\Http\Controllers;

use App\Models\MemorialMessage;
use App\Models\Report;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Readers flagging something for a moderator to look at.
 *
 * Reporting hides nothing by itself — a single click shouldn't be able to
 * remove someone's words. It puts the item in the /admin queue for a person
 * to judge.
 */
class ReportController extends Controller
{
    /** Only these can be reported; the type comes from the form, so it's a whitelist. */
    private const TYPES = [
        'memorial-message' => MemorialMessage::class,
        'review' => Review::class,
    ];

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'id' => ['required', 'integer'],
            'reason' => ['required', Rule::in(array_keys(Report::REASONS))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $model = self::TYPES[$validated['type']];
        $reportable = $model::findOrFail($validated['id']);

        // updateOrCreate, so reporting twice updates the reason rather than
        // tripping the unique index with a 500.
        Report::updateOrCreate(
            [
                'reportable_type' => $reportable->getMorphClass(),
                'reportable_id' => $reportable->id,
                'user_id' => $request->user()->id,
            ],
            [
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'open',
            ],
        );

        return back()->with('success', 'Thanks — a moderator will take a look.');
    }
}
