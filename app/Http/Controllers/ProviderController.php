<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\Species;
use Illuminate\View\View;

class ProviderController extends Controller
{
    /** Public provider profile with the booking panel. */
    public function show(ProviderProfile $provider): View
    {
        $this->authorize('view', $provider);

        $provider->load([
            'user',
            'photos',
            'services' => fn ($q) => $q->active()->with('category'),
        ]);

        return view('providers.show', [
            'provider' => $provider,
            'species' => Species::orderBy('name')->get(),
            'blockedDates' => $provider->unavailableDates()
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->pluck('date')
                ->map(fn ($date) => $date->toDateString()),
        ]);
    }
}
