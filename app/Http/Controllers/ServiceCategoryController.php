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
        // Any submitted search — even an empty one, i.e. "Any service" — goes straight
        // to results. A bare visit to /services has none of these keys and stays here.
        if ($request->hasAny(['q', 'location', 'category'])) {
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
        // Categories can come from the route (/services/{category}) and/or the
        // multi-select filter (?category[]=…). An empty selection means "any".
        $categorySlugs = collect((array) $request->input('category', []))
            ->push($category?->slug)
            ->filter(fn ($slug) => filled($slug))
            ->unique()
            ->values()
            ->all();

        $providers = ProviderProfile::query()
            ->approved()
            ->published()
            ->with(['user', 'photos', 'services.category'])
            ->when($categorySlugs !== [], fn ($q) => $q->inCategories($categorySlugs))
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
                'category' => $categorySlugs,
            ],
        ]);
    }
}
