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
    public function definition(): array
    {
        $name = fake()->firstName();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'age_group' => fake()->randomElement(['baby', 'young', 'adult', 'senior']),
            'age_months' => fake()->numberBetween(2, 120),
            'gender' => fake()->randomElement(['male', 'female']),
            'size' => fake()->randomElement(['small', 'medium', 'large']),
            'coat' => fake()->randomElement(['short', 'medium', 'long']),
            'color' => fake()->randomElement(['Black', 'White', 'Brown', 'Tabby', 'Golden', 'Grey']),
            'secondary_color' => fake()->optional()->randomElement(['White', 'Black', 'Brown']),
            'status' => 'available',
            'adoption_fee' => fake()->randomElement([0, 25, 50, 75, 100]),
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
            'location' => fake()->city().', '.fake()->stateAbbr(),
            'description' => fake()->paragraphs(2, true),
            'published_at' => now(),
            'status_changed_at' => now(),
        ];
    }

    public function adopted(): static
    {
        return $this->state(fn () => ['status' => 'adopted', 'status_changed_at' => now()]);
    }
}
