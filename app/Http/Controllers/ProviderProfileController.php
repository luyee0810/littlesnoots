<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderProfileRequest;
use App\Models\Species;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $this->authorize('update', $profile);

        return view('provider.profile', [
            'profile' => $profile,
            'species' => Species::orderBy('name')->get(),
        ]);
    }

    public function update(StoreProviderProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        $this->authorize('update', $profile);

        $profile->update($request->safe()->except('slug'));

        return redirect()
            ->route('provider.profile.edit')
            ->with('success', 'Profile updated.');
    }

    /** A suspended sitter asking for another look after making changes. */
    public function resubmit(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile !== null, 404);

        $profile->submitForReview();

        return back()->with('success', 'Your profile is back in the queue — we’ll take another look shortly.');
    }
}
