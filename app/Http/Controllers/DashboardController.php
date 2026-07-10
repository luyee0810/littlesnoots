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

        if ($user->isStaff()) {
            return view('dashboard', [
                'listedPets' => $user->listedPets()->with('photos')->latest()->get(),
            ]);
        }

        return view('dashboard', [
            'applications' => $user->adoptionApplications()->with('pet.photos')->latest()->get(),
        ]);
    }
}
