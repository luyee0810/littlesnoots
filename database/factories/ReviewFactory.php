<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rating' => fake()->randomElement([5, 5, 5, 4, 4, 3]),
            'body' => fake()->optional(0.85)->sentence(14),
        ];
    }

    /** Review a specific booking, taking the provider and owner from it. */
    public function forBooking(Booking $booking): static
    {
        return $this->state(fn () => [
            'booking_id' => $booking->id,
            'provider_profile_id' => $booking->provider_profile_id,
            'user_id' => $booking->user_id,
        ]);
    }
}
