<?php

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    /** Klang Valley + a few other Malaysian areas, paired with their state. */
    private const LOCATIONS = [
        ['Kuala Lumpur', 'Kuala Lumpur', '50'],
        ['Bangsar', 'Kuala Lumpur', '59'],
        ['Mont Kiara', 'Kuala Lumpur', '50'],
        ['Cheras', 'Kuala Lumpur', '56'],
        ['Petaling Jaya', 'Selangor', '46'],
        ['Subang Jaya', 'Selangor', '47'],
        ['Shah Alam', 'Selangor', '40'],
        ['Puchong', 'Selangor', '47'],
        ['Ampang', 'Selangor', '68'],
        ['George Town', 'Pulau Pinang', '10'],
        ['Johor Bahru', 'Johor', '80'],
    ];

    public function definition(): array
    {
        $headlines = [
            'Cat-obsessed sitter with a quiet spare room',
            'Daily dog walks around the neighbourhood, rain or shine',
            'Gentle grooming for nervous pets',
            'Weekend boarding in a landed home with a garden',
            'Retired vet nurse offering overnight care',
            'Puppy training and socialisation',
            'Reliable pet taxi across the Klang Valley',
        ];

        [$city, $state, $postPrefix] = fake()->randomElement(self::LOCATIONS);

        return [
            'user_id' => User::factory(),
            'slug' => 'sitter-'.fake()->unique()->numberBetween(1000, 9999),
            'headline' => fake()->randomElement($headlines),
            'bio' => self::bio(),
            'address1' => fake()->buildingNumber().' Jalan '.fake()->lastName(),
            'city' => $city,
            'state' => $state,
            'postcode' => $postPrefix.fake()->numerify('###'),
            'country' => 'MY',
            'latitude' => fake()->latitude(2.9, 3.3),
            'longitude' => fake()->longitude(101.4, 101.8),
            'service_radius_km' => fake()->randomElement([5, 10, 15, 25]),
            'years_experience' => fake()->numberBetween(0, 15),
            'home_type' => fake()->randomElement(['condominium', 'landed house', 'apartment']),
            'has_fenced_yard' => fake()->boolean(50),
            'has_own_pets' => fake()->boolean(60),
            'is_smoke_free' => fake()->boolean(90),
            'has_insurance' => fake()->boolean(40),
            'accepts_species' => fake()->randomElements(['cat', 'dog', 'rabbit'], fake()->numberBetween(1, 3)),
            'accepts_sizes' => fake()->randomElements(['small', 'medium', 'large'], fake()->numberBetween(1, 3)),
            'max_pets_per_booking' => fake()->numberBetween(1, 4),
            'available_days' => fake()->randomElements(
                ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                fake()->numberBetween(4, 7)
            ),
            'status' => 'approved',
            'published_at' => now(),
            'verified_email_at' => now(),
            // Rating and count come from real reviews — see ProviderProfile::refreshRating().
            'rating_avg' => 0,
            'reviews_count' => 0,
            'bookings_count' => fake()->numberBetween(0, 120),
        ];
    }

    /** A readable sitter bio — lorem ipsum makes the demo listings unusable. */
    private static function bio(): string
    {
        $opening = fake()->randomElement([
            'I have kept cats and dogs at home since I was a kid, and pet-sitting is how I spend my weekends.',
            'I work from home in the Klang Valley, so there is always someone around to keep your pet company.',
            'I grew up helping out at a local rescue and have been fostering strays for years.',
            'My own two rescues run the house, and your pet is welcome to join the pack.',
        ]);

        $detail = fake()->randomElement([
            'Your pet gets fed on their usual schedule, plenty of playtime, and photo updates every day.',
            'I send a short WhatsApp update with photos each evening so you know how the day went.',
            'I am comfortable with medication, senior pets, and anxious rescues who need a slow introduction.',
            'Air-conditioned room, no other pets around if yours prefers peace and quiet.',
        ]);

        $closing = fake()->randomElement([
            'Happy to do a meet-and-greet first — just message me.',
            'A vet clinic is five minutes away in case anything comes up.',
            'Drop-off and pick-up can be arranged around the Klang Valley for a small fee.',
            'Do let me know about any quirks or routines and I will follow them exactly.',
        ]);

        return "{$opening} {$detail} {$closing}";
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'slug' => Str::slug($user->name).'-'.fake()->unique()->numberBetween(100, 999),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => ['status' => 'pending', 'published_at' => null]);
    }
}
