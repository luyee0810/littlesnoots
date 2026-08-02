<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderProfileRequest;
use App\Models\ProviderProfile;
use App\Models\ServiceCategory;
use App\Models\Species;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProviderOnboardingController extends Controller
{
    /** "Become a sitter" — one form covering the profile basics. */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasProviderProfile()) {
            return redirect()->route('provider.services.index');
        }

        return view('provider.onboarding', [
            'species' => Species::orderBy('name')->get(),
            'categories' => ServiceCategory::active()->ordered()->get(),
        ]);
    }

    public function store(StoreProviderProfileRequest $request): RedirectResponse
    {
        $this->authorize('create', ProviderProfile::class);

        $user = $request->user();

        $profile = ProviderProfile::create([
            ...$request->safe()->except('slug'),
            'user_id' => $user->id,
            'slug' => $this->uniqueSlug($user->name),
            // Phase 2 has no admin review queue yet, so a new profile goes live
            // immediately. 2b introduces the `pending` → `approved` step.
            'status' => 'approved',
            'published_at' => now(),
        ]);

        return redirect()
            ->route('provider.services.create')
            ->with('success', 'Profile created. Now add the services you offer.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'sitter';
        $slug = $base;
        $i = 2;

        while (ProviderProfile::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
