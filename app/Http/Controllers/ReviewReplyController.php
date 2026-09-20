<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** A sitter's single public answer to a review of them. */
class ReviewReplyController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->canBeRepliedToBy($request->user()), 403);

        $validated = $request->validate([
            'provider_reply' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $review->reply($validated['provider_reply']);

        return back()->with('success', 'Your reply has been posted.');
    }
}
