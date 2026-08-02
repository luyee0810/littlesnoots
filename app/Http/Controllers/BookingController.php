<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ProviderProfile;
use DateTimeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BookingController extends Controller
{
    /** Owner-side booking request, submitted from a provider profile. */
    public function store(StoreBookingRequest $request, ProviderProfile $provider): RedirectResponse
    {
        if (! $provider->isLive()) {
            return back()->with('error', 'This provider is not currently taking bookings.');
        }

        $service = $request->serviceOrNull();
        $category = $service->category;

        $starts = $request->date('starts_at');
        $ends = $request->date('ends_at');
        $units = $this->unitsFor($category->pricing_unit, $starts, $ends, $service->min_units);
        $pets = $request->integer('pet_count');

        $booking = Booking::create([
            ...$request->safe()->except(['provider_service_id']),
            'provider_profile_id' => $provider->id,
            'provider_service_id' => $service->id,
            'service_category_id' => $category->id,
            'user_id' => $request->user()->id,
            'unit_quantity' => $units,
            'unit_label' => $category->pricing_unit,
            // Price is always calculated here — never taken from the form.
            'unit_price' => $service->price,
            'additional_pet_price' => $service->additional_pet_price,
            'total' => $service->totalFor($units, $pets),
            'currency' => $service->currency,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', "Request sent to {$provider->user->name}. You'll hear back within ".Booking::RESPONSE_WINDOW_HOURS.' hours.');
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['providerProfile.user', 'providerProfile.photos', 'service', 'category', 'user']);

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

    /**
     * How many billable units a booking covers. Date-range services (boarding, daycare)
     * bill per night/day; everything else is a single session, walk or trip.
     */
    private function unitsFor(string $unit, ?DateTimeInterface $starts, ?DateTimeInterface $ends, int $minUnits): int
    {
        if ($starts === null || $ends === null || ! in_array($unit, ['night', 'day'], true)) {
            return max($minUnits, 1);
        }

        $days = (int) Carbon::instance($starts)->startOfDay()
            ->diffInDays(Carbon::instance($ends)->startOfDay());

        return max($days, $minUnits, 1);
    }
}
