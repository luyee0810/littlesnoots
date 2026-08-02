<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Member dashboard — applications for adopters, listings for rehomers. */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Service bookings are shown to everyone — adopter or rehomer, anyone can
        // book a sitter (Phase 2).
        $shared = [
            'bookings' => $user->bookings()
                ->with(['providerProfile.user', 'category'])
                ->latest()
                ->get(),
            'providerProfile' => $user->providerProfile,
        ];

        if ($user->isStaff()) {
            return view('dashboard', [
                ...$shared,
                'listedPets' => $user->listedPets()->with('photos')->latest()->get(),
            ]);
        }

        return view('dashboard', [
            ...$shared,
            'applications' => $user->adoptionApplications()->with('pet.photos')->latest()->get(),
        ]);
    }
}
