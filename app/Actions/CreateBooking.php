<?php

namespace App\Actions;

use App\Exceptions\ProviderNotBookableException;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Creates a pending booking from already-validated input.
 *
 * Deliberately free of any HTTP types so the web controller and a future API
 * controller share one implementation — pricing must never be duplicated
 * across callers, or the two can drift apart.
 */
class CreateBooking
{
    /**
     * @param  array<string, mixed>  $attributes  Validated booking fields, excluding provider_service_id.
     *
     * @throws ProviderNotBookableException
     */
    public function handle(
        User $user,
        ProviderProfile $provider,
        ProviderService $service,
        array $attributes,
    ): Booking {
        if (! $provider->isLive()) {
            throw new ProviderNotBookableException;
        }

        $category = $service->category;

        $starts = $this->toDate($attributes['starts_at'] ?? null);
        $ends = $this->toDate($attributes['ends_at'] ?? null);
        $units = $this->unitsFor($category->pricing_unit, $starts, $ends, $service->min_units);
        $pets = (int) ($attributes['pet_count'] ?? 1);

        return Booking::create([
            ...$attributes,
            'provider_profile_id' => $provider->id,
            'provider_service_id' => $service->id,
            'service_category_id' => $category->id,
            'user_id' => $user->id,
            'unit_quantity' => $units,
            'unit_label' => $category->pricing_unit,
            // Price is always calculated here — never taken from the caller.
            'unit_price' => $service->price,
            'additional_pet_price' => $service->additional_pet_price,
            'total' => $service->totalFor($units, $pets),
            'currency' => $service->currency,
            'status' => 'pending',
        ]);
    }

    /** Accepts whatever the caller has — a Carbon, a date string, or nothing. */
    private function toDate(mixed $value): ?DateTimeInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof DateTimeInterface ? $value : Carbon::parse($value);
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
