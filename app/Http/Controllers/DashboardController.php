<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Member dashboard — bookings, adoption activity, and anything waiting on them. */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $bookings = $user->bookings()
            ->with(['providerProfile.user', 'category', 'review'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->unreadFor($user)])
            ->latest()
            ->get();

        // "Upcoming" means still live — pending, accepted or under way. A booking
        // without dates (a walk, a one-off session) counts until it's closed.
        [$upcoming, $past] = $bookings->partition(
            fn (Booking $booking) => in_array($booking->status, ['pending', 'accepted', 'in_progress'], true)
        );

        $shared = [
            'upcoming' => $upcoming->sortBy(fn (Booking $b) => $b->starts_at ?? $b->created_at)->values(),
            'past' => $past,
            // Completed bookings the owner hasn't reviewed — the nudge that
            // keeps sitter ratings meaningful.
            'awaitingReview' => $past->filter(
                fn (Booking $booking) => $booking->status === 'completed' && $booking->review === null
            ),
            'unreadTotal' => $bookings->sum('unread_count'),
            'providerProfile' => $user->providerProfile,
            // Listings live at /rehome — duplicating them here just meant two
            // places to keep in step.
            'hasListings' => $user->listedPets()->exists(),
        ];

        return view('dashboard', [
            ...$shared,
            'applications' => $user->adoptionApplications()->with('pet.photos')->latest()->get(),
        ]);
    }
}
