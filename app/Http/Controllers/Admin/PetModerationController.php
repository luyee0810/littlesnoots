<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Notifications\PetListingApproved;
use App\Notifications\PetListingNeedsChanges;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The moderation queue — staff reviewing listings created by rescuers.
 *
 * Approving is what publishes a listing: Pet::markApproved() sets published_at,
 * which the public published() scope gates on.
 */
class PetModerationController extends Controller
{
    /** Submitted listings first — that's the work. Everything else is filterable. */
    public function index(Request $request): View
    {
        $filter = $request->query('status', 'submitted');

        $pets = Pet::query()
            ->with(['photos', 'species', 'lister', 'organization'])
            ->withCount('applications')
            ->when(
                in_array($filter, ['draft', 'submitted', 'approved', 'rejected'], true),
                fn ($q) => $q->where('review_status', $filter),
            )
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderByRaw("case when review_status = 'submitted' then 0 else 1 end")
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pets.index', [
            'pets' => $pets,
            'filter' => $filter,
            'counts' => Pet::query()
                ->selectRaw('review_status, count(*) as total')
                ->groupBy('review_status')
                ->pluck('total', 'review_status'),
        ]);
    }

    /** The full listing as submitted, for a moderator to read before deciding. */
    public function show(Pet $pet): View
    {
        return view('admin.pets.show', [
            'pet' => $pet->load(['photos', 'species', 'breed', 'secondaryBreed', 'lister', 'organization', 'reviewer']),
        ]);
    }

    public function approve(Request $request, Pet $pet): RedirectResponse
    {
        $pet->markApproved($request->user());

        Notify::send($pet->lister, new PetListingApproved($pet));

        return redirect()
            ->route('admin.pets.index')
            ->with('success', "{$pet->name} is now live on the site.");
    }

    public function reject(Request $request, Pet $pet): RedirectResponse
    {
        $validated = $request->validate([
            // The lister sees this, so it has to say what needs fixing.
            'review_notes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $pet->markRejected($request->user(), $validated['review_notes']);

        Notify::send($pet->lister, new PetListingNeedsChanges($pet, $validated['review_notes']));

        return redirect()
            ->route('admin.pets.index')
            ->with('success', "Sent back to {$pet->lister?->name} with your notes.");
    }

    /** Pull a live listing back off the site without deleting it. */
    public function unpublish(Request $request, Pet $pet): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $pet->markRejected($request->user(), $validated['review_notes']);

        Notify::send($pet->lister, new PetListingNeedsChanges($pet, $validated['review_notes']));

        return back()->with('success', "{$pet->name} has been unpublished.");
    }
}
