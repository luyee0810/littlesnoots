<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\ProviderService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $starts = fake()->dateTimeBetween('-2 months', '+2 months');
        $units = fake()->numberBetween(1, 5);
        $price = fake()->numberBetween(30, 90);

        return [
            // Set here as well as in the model's `creating` hook: seeders run under
            // WithoutModelEvents, which would otherwise leave these null.
            'reference' => Booking::generateReference(),
            'expires_at' => now()->addHours(Booking::RESPONSE_WINDOW_HOURS),
            'provider_service_id' => ProviderService::factory(),
            'user_id' => User::factory(),
            'starts_at' => $starts,
            'ends_at' => (clone $starts)->modify("+{$units} days"),
            'unit_quantity' => $units,
            'unit_label' => 'night',
            'pet_name' => fake()->firstName(),
            'pet_breed' => fake()->randomElement(['Domestic Shorthair', 'Poodle', 'Beagle', 'Persian']),
            'pet_size' => fake()->randomElement(['small', 'medium', 'large']),
            'pet_count' => 1,
            'pet_notes' => fake()->optional()->sentence(),
            'owner_name' => fake()->name(),
            'owner_email' => fake()->safeEmail(),
            'owner_phone' => '01'.fake()->numerify('#-###-####'),
            'unit_price' => $price,
            'total' => $price * $units,
            'currency' => 'MYR',
            'status' => 'pending',
            'message' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Derive the booking's denormalised columns from a real service row.
     * Units and pets are passed in rather than read from `$attributes` — state closures
     * see the factory defaults, not the overrides given to create().
     */
    public function forService(ProviderService $service, int $units = 1, int $pets = 1): static
    {
        return $this->state(fn () => [
            'provider_service_id' => $service->id,
            'provider_profile_id' => $service->provider_profile_id,
            'service_category_id' => $service->service_category_id,
            'unit_quantity' => $units,
            'unit_label' => $service->price_unit,
            'pet_count' => $pets,
            'unit_price' => $service->price,
            'additional_pet_price' => $service->additional_pet_price,
            'total' => $service->totalFor($units, $pets),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status' => 'accepted', 'responded_at' => now()->subDays(2)]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'responded_at' => now()->subDays(10),
            'completed_at' => now()->subDays(3),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn () => [
            'status' => 'declined',
            'responded_at' => now()->subDays(1),
            'provider_response' => 'Sorry, I am fully booked that week.',
        ]);
    }
}
