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
            // Staff approve sitters before owners can find them. The profile is
            // usable meanwhile — services can be added while it waits.
            'status' => 'pending',
            'published_at' => null,
        ]);

        return redirect()
            ->route('provider.services.create')
            ->with('success', 'Profile created. Add the services you offer — we’ll review your profile before it goes live.');
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
