<?php

namespace Database\Factories;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pet>
 */
class PetFactory extends Factory
{
    /** Pet names you'd actually hear at a Malaysian shelter. */
    private const NAMES = [
        'Comel', 'Tompok', 'Oyen', 'Bulat', 'Manja', 'Mochi', 'Kopi', 'Milo', 'Teh Tarik',
        'Buddy', 'Luna', 'Simba', 'Bella', 'Coco', 'Nala', 'Bubbles', 'Popo', 'Momo',
        'Ginger', 'Puteh', 'Hitam', 'Belang', 'Chubby', 'Snowy', 'Roti', 'Lily', 'Rusty',
        'Peanut', 'Mimi', 'Boboi', 'Kiki', 'Suki', 'Zara', 'Rocky', 'Daisy', 'Pepper',
    ];

    /** [city, state] — Klang Valley plus a few other Malaysian hubs. */
    private const LOCATIONS = [
        ['Kuala Lumpur', 'Kuala Lumpur'],
        ['Cheras', 'Kuala Lumpur'],
        ['Setapak', 'Kuala Lumpur'],
        ['Petaling Jaya', 'Selangor'],
        ['Subang Jaya', 'Selangor'],
        ['Shah Alam', 'Selangor'],
        ['Puchong', 'Selangor'],
        ['Klang', 'Selangor'],
        ['George Town', 'Pulau Pinang'],
        ['Johor Bahru', 'Johor'],
        ['Ipoh', 'Perak'],
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(self::NAMES);
        [$city, $state] = fake()->randomElement(self::LOCATIONS);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'age_group' => $ageGroup = fake()->randomElement(['young', 'adult', 'senior']),
            // Keep the month count inside the band the age group claims.
            'age_months' => match ($ageGroup) {
                'young' => fake()->numberBetween(3, 11),
                'adult' => fake()->numberBetween(12, 84),
                default => fake()->numberBetween(85, 168),
            },
            'gender' => fake()->randomElement(['male', 'female']),
            'size' => fake()->randomElement(['small', 'medium', 'large']),
            'coat' => fake()->randomElement(['short', 'medium', 'long']),
            'color' => fake()->randomElement(['Black', 'White', 'Brown', 'Tabby', 'Orange', 'Golden', 'Grey', 'Calico']),
            'secondary_color' => fake()->optional()->randomElement(['White', 'Black', 'Brown']),
            'status' => 'available',
            // Ringgit — Malaysian shelters usually charge a small fee, or nothing at all.
            'adoption_fee' => fake()->randomElement([0, 0, 50, 80, 100, 150, 200]),
            'spayed_neutered' => fake()->boolean(70),
            'shots_current' => fake()->boolean(80),
            'house_trained' => fake()->boolean(60),
            'declawed' => false,
            'special_needs' => fake()->boolean(10),
            'good_with_children' => fake()->boolean(70),
            'good_with_dogs' => fake()->boolean(60),
            'good_with_cats' => fake()->boolean(50),
            'breed_mixed' => fake()->boolean(30),
            'tags' => fake()->randomElements(
                ['Friendly', 'Playful', 'Affectionate', 'Gentle', 'Curious', 'Loyal', 'Calm', 'Smart'],
                fake()->numberBetween(2, 4)
            ),
            'location' => $city.', '.$state,
            'description' => self::blurb($name),
            'published_at' => now(),
            'status_changed_at' => now(),
        ];
    }

    /** A short, readable blurb — lorem ipsum makes demo listings unreadable. */
    private static function blurb(string $name): string
    {
        $intro = fake()->randomElement([
            "{$name} came to us as a stray and has been thriving in foster care ever since.",
            "{$name} was surrendered when the family moved overseas and is now looking for a new home.",
            "{$name} was rescued from a back lane in the neighbourhood, thin and scared, but has bounced back beautifully.",
            "{$name} was born in our foster carer's home and has been handled by people since day one.",
        ]);

        $middle = fake()->randomElement([
            'Loves a warm lap, an afternoon nap by the window, and being talked to.',
            'Playful in the evenings, then completely happy to nap the rest of the day away.',
            'Confident with visitors and settles quickly in a new place.',
            'A little shy at first, but comes right up for a chin scratch once trust is built.',
        ]);

        $close = fake()->randomElement([
            'Fully vaccinated and dewormed, and ready to go home.',
            'Would do best in a home with a screened balcony or windows.',
            'Comes with a starter bag of food and a vet record book.',
            'Our team is happy to answer any questions before you decide.',
        ]);

        return "{$intro} {$middle} {$close}";
    }

    public function adopted(): static
    {
        return $this->state(fn () => ['status' => 'adopted', 'status_changed_at' => now()]);
    }
}
