<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemorialMessageRequest;
use App\Models\MemorialMessage;
use App\Models\PetMemorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemorialMessageController extends Controller
{
    public function store(StoreMemorialMessageRequest $request, PetMemorial $memorial): RedirectResponse
    {
        $memorial->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()
            ->route('memorials.show', $memorial)
            ->with('success', 'Your message has been added.')
            ->withFragment('guestbook');
    }

    /**
     * Remove a guestbook message.
     *
     * The memorial's owner moderates their own tribute page — a hurtful message
     * on a page about a dead pet shouldn't wait on staff. Authors can also take
     * back their own words.
     */
    public function destroy(Request $request, MemorialMessage $message): RedirectResponse
    {
        abort_unless($message->isDeletableBy($request->user()), 403);

        $memorial = $message->memorial;

        // Any open reports about it are moot once it's gone.
        $message->reports()->update([
            'status' => 'actioned',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $message->delete();

        return redirect()
            ->route('memorials.show', $memorial)
            ->with('success', 'Message removed.')
            ->withFragment('guestbook');
    }
}
