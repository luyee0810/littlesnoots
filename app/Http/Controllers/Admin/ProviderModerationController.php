<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Approving sitters. A profile stays invisible to owners until a moderator
 * approves it — ProviderProfile::isLive() requires both `approved` and
 * `published_at`, and every public listing scope goes through it.
 */
class ProviderModerationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('status', 'pending');

        $providers = ProviderProfile::query()
            ->with(['user', 'services.category', 'photos'])
            ->withCount(['bookings', 'reviews'])
            ->when(
                in_array($filter, ['draft', 'pending', 'approved', 'suspended'], true),
                fn ($q) => $q->where('status', $filter),
            )
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')))
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.providers.index', [
            'providers' => $providers,
            'filter' => $filter,
            'counts' => ProviderProfile::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function show(ProviderProfile $provider): View
    {
        return view('admin.providers.show', [
            'provider' => $provider->load(['user', 'services.category', 'photos', 'reviewer']),
        ]);
    }

    public function approve(Request $request, ProviderProfile $provider): RedirectResponse
    {
        $provider->markApproved($request->user());

        return redirect()
            ->route('admin.providers.index')
            ->with('success', "{$provider->user->name} is now listed as a sitter.");
    }

    /** Covers both "not yet good enough" and "take this one down". */
    public function suspend(Request $request, ProviderProfile $provider): RedirectResponse
    {
        $validated = $request->validate([
            // The sitter sees this, so it has to say what needs fixing.
            'review_notes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $provider->markSuspended($request->user(), $validated['review_notes']);

        return redirect()
            ->route('admin.providers.index')
            ->with('success', "{$provider->user->name}'s profile is no longer listed.");
    }
}
