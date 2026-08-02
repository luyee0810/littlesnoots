<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderServiceRequest;
use App\Models\ProviderService;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderServiceController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        return view('provider.services.index', [
            'profile' => $profile,
            'services' => $profile->services()->with('category')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        return view('provider.services.form', [
            'service' => new ProviderService(['min_units' => 1, 'max_pets' => $profile->max_pets_per_booking]),
            'categories' => $this->availableCategories($request),
        ]);
    }

    public function store(StoreProviderServiceRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        $category = ServiceCategory::findOrFail($request->integer('service_category_id'));

        $profile->services()->create([
            ...$request->validated(),
            // The unit belongs to the category — a provider picks the price, not the unit.
            'price_unit' => $category->pricing_unit,
            'currency' => 'MYR',
        ]);

        return redirect()
            ->route('provider.services.index')
            ->with('success', "{$category->name} added to your profile.");
    }

    public function edit(Request $request, ProviderService $service): View
    {
        $this->authorizeService($request, $service);

        return view('provider.services.form', [
            'service' => $service,
            'categories' => $this->availableCategories($request, $service),
        ]);
    }

    public function update(StoreProviderServiceRequest $request, ProviderService $service): RedirectResponse
    {
        $this->authorizeService($request, $service);

        $category = ServiceCategory::findOrFail($request->integer('service_category_id'));

        $service->update([
            ...$request->validated(),
            'price_unit' => $category->pricing_unit,
        ]);

        return redirect()
            ->route('provider.services.index')
            ->with('success', 'Service updated.');
    }

    public function destroy(Request $request, ProviderService $service): RedirectResponse
    {
        $this->authorizeService($request, $service);

        // Bookings reference the service row (restrictOnDelete), so retire rather than
        // delete once it has history.
        if ($service->bookings()->exists()) {
            $service->update(['is_active' => false]);

            return back()->with('success', 'Service hidden. Past bookings keep their details.');
        }

        $service->delete();

        return back()->with('success', 'Service removed.');
    }

    private function authorizeService(Request $request, ProviderService $service): void
    {
        abort_unless(
            $service->provider_profile_id === $request->user()->providerProfile?->id,
            403,
        );
    }

    /** Categories the provider hasn't already listed (plus the one being edited). */
    private function availableCategories(Request $request, ?ProviderService $editing = null)
    {
        $taken = $request->user()->providerProfile
            ->services()
            ->when($editing, fn ($q) => $q->whereKeyNot($editing->id))
            ->pluck('service_category_id');

        return ServiceCategory::active()->ordered()->whereNotIn('id', $taken)->get();
    }
}
