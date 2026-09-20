<?php

namespace App\Http\Controllers;

use App\Http\Requests\RespondToBookingRequest;
use App\Models\Booking;
use App\Notifications\BookingAnswered;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderBookingController extends Controller
{
    /** The provider's inbox — pending requests first, then everything else. */
    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $bookings = Booking::forProvider($profile)
            ->with(['user', 'category', 'service'])
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->get();

        return view('provider.bookings', [
            'profile' => $profile,
            'pending' => $bookings->where('status', 'pending'),
            'bookings' => $bookings->where('status', '!=', 'pending'),
        ]);
    }

    public function accept(RespondToBookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->markAccepted($request->input('provider_response'));

        $this->notifyOwner($booking);

        return back()->with('success', "Booking {$booking->reference} confirmed. {$booking->owner_name} has been notified.");
    }

    public function decline(RespondToBookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->markDeclined($request->input('provider_response'));

        $this->notifyOwner($booking);

        return back()->with('success', "Booking {$booking->reference} declined.");
    }

    public function complete(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('complete', $booking);

        if ($booking->status === 'accepted') {
            $booking->markInProgress();
        }

        $booking->markCompleted();

        return back()->with('success', "Booking {$booking->reference} marked as completed.");
    }

    /** The owner may have booked as a guest, so fall back to the snapshot email. */
    private function notifyOwner(Booking $booking): void
    {
        $booking->refresh();

        $booking->user
            ? Notify::send($booking->user, new BookingAnswered($booking))
            : Notify::toEmail($booking->owner_email, new BookingAnswered($booking));
    }
}
