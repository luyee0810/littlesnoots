<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Models\ProviderProfile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Back-of-house overview: what needs attention, and how big the site is. */
class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('admin.dashboard', [
            'queue' => Pet::awaitingReview()->with(['photos', 'lister', 'species'])->take(5)->get(),
            'awaitingReview' => Pet::awaitingReview()->count(),
            'pendingProviders' => ProviderProfile::awaitingApproval()->count(),
            'pendingApplications' => AdoptionApplication::where('status', 'pending')->count(),
            'openReports' => Report::open()->count(),
            'livePets' => Pet::published()->available()->count(),
            'totalUsers' => User::count(),
        ]);
    }
}
