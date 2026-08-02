<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    /** Services landing page — the category grid plus the shared search bar. */
    public function index(Request $request): View
    {
        // A keyword or location search from the landing page goes straight to results.
        if ($request->filled('q') || $request->filled('location') || $request->filled('category')) {
            return $this->results($request, null);
        }

        return view('services.index', [
            'categories' => ServiceCategory::active()->ordered()->withCount('providerServices')->get(),
            'filters' => $request->only(['q', 'location', 'category']),
            'featured' => ProviderProfile::query()
                ->approved()->published()
                ->with(['user', 'photos', 'services.category'])
                ->orderByDesc('rating_avg')
                ->take(3)
                ->get(),
        ]);
    }

    /** Providers within one category. */
    public function show(Request $request, ServiceCategory $category): View
    {
        return $this->results($request, $category);
    }

    /**
     * Phase 2 search: keywords, location, service category. Nothing else — the other
     * columns exist but are deliberately not exposed as facets yet.
     */
    private function results(Request $request, ?ServiceCategory $category): View
    {
        $categorySlug = $category?->slug ?? $request->string('category')->toString();

        $providers = ProviderProfile::query()
            ->approved()
            ->published()
            ->with(['user', 'photos', 'services.category'])
            ->when($categorySlug !== '', fn ($q) => $q->inCategory($categorySlug))
            ->when($request->filled('location'), fn ($q) => $q->inLocation($request->string('location')))
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')))
            ->orderByDesc('rating_avg')
            ->orderByDesc('bookings_count')
            ->paginate(12)
            ->withQueryString();

        return view('services.show', [
            'category' => $category,
            'categories' => ServiceCategory::active()->ordered()->get(),
            'providers' => $providers,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'location' => $request->string('location')->toString(),
                'category' => $categorySlug,
            ],
        ]);
    }
}
