<?php

namespace App\Http\Controllers;

use App\Actions\CreateBooking;
use App\Exceptions\ProviderNotBookableException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Notifications\BookingRequested;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /** Owner-side booking request, submitted from a provider profile. */
    public function store(
        StoreBookingRequest $request,
        ProviderProfile $provider,
        CreateBooking $createBooking,
    ): RedirectResponse {
        try {
            $booking = $createBooking->handle(
                $request->user(),
                $provider,
                $request->serviceOrNull(),
                $request->safe()->except(['provider_service_id']),
            );
        } catch (ProviderNotBookableException $e) {
            return back()->with('error', $e->getMessage());
        }

        // The sitter has a limited window to answer, so tell them now.
        Notify::send($provider->user, new BookingRequested($booking));

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', "Request sent to {$provider->user->name}. You'll hear back within ".Booking::RESPONSE_WINDOW_HOURS.' hours.');
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load([
            'providerProfile.user', 'providerProfile.photos', 'service', 'category', 'user',
            'messages.user',
        ]);

        // Opening the thread is reading it.
        if ($booking->isParticipant($request->user())) {
            $booking->markMessagesReadFor($request->user());
        }

        return view('bookings.show', ['booking' => $booking]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $isOwner = $booking->user_id === $request->user()->id;

        $isOwner
            ? $booking->markCancelledByOwner()
            : $booking->markCancelledByProvider();

        return back()->with('success', "Booking {$booking->reference} has been cancelled.");
    }
}
