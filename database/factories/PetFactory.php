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
            'sex' => fake()->randomElement(['male', 'female']),
            'size' => fake()->randomElement(['small', 'medium', 'large']),
            'age_months' => fake()->numberBetween(2, 120),
            'color' => fake()->randomElement(['Black', 'White', 'Brown', 'Tabby', 'Golden', 'Grey']),
            'status' => 'available',
            'adoption_fee' => fake()->randomElement([0, 25, 50, 75, 100]),
            'vaccinated' => fake()->boolean(80),
            'sterilized' => fake()->boolean(70),
            'good_with_kids' => fake()->boolean(),
            'good_with_pets' => fake()->boolean(),
            'location' => fake()->randomElement(['Two Fat Cats Shelter', 'Downtown Foster', 'North Branch']),
            'description' => fake()->paragraph(),
            'published_at' => now(),
        ];
    }

    public function adopted(): static
    {
        return $this->state(fn () => ['status' => 'adopted']);
    }
}
