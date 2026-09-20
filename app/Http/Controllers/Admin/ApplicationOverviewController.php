<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdoptionApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Every application across the whole site.
 *
 * Deciding still happens on the pet's own page — this is the overview that
 * tells staff which rescuers have people waiting on them.
 */
class ApplicationOverviewController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('status', 'open');

        $applications = AdoptionApplication::query()
            ->with(['pet.photos', 'pet.lister', 'user'])
            ->when($filter === 'open', fn ($q) => $q->open())
            ->when(
                in_array($filter, ['pending', 'reviewing', 'approved', 'rejected', 'withdrawn'], true),
                fn ($q) => $q->where('status', $filter),
            )
            ->orderByRaw("case when status = 'pending' then 0 when status = 'reviewing' then 1 else 2 end")
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'filter' => $filter,
            'counts' => AdoptionApplication::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }
}
