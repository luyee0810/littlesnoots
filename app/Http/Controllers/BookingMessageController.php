<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Notifications\BookingMessageReceived;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Owner ↔ sitter chat, scoped to a booking. */
class BookingMessageController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        // Only the two people in the booking may write — an admin can read the
        // thread during a dispute, but shouldn't be able to post as a party.
        abort_unless($booking->isParticipant($request->user()), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $booking->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $recipient = $booking->counterpartFor($request->user());

        Notify::send($recipient, new BookingMessageReceived($booking, $request->user()));

        return redirect()
            ->route('bookings.show', $booking)
            ->withFragment('messages');
    }
}
