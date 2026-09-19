<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /** Owner reviews a completed booking; the provider's rating is recomputed. */
    public function store(StoreReviewRequest $request, Booking $booking): RedirectResponse
    {
        $provider = $booking->providerProfile;

        DB::transaction(function () use ($request, $booking, $provider) {
            $booking->review()->create([
                ...$request->validated(),
                'provider_profile_id' => $provider->id,
                'user_id' => $request->user()->id,
            ]);

            $provider->refreshRating();
        });

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', "Thanks — your review is now on {$provider->user->name}’s profile.");
    }
}
