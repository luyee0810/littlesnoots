<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Pet Boarding', 'House Sitting', 'Dog Walking', 'Pet Daycare',
            'Pet Grooming', 'Pet Taxi', 'Pet Training',
        ]);

        $unit = match ($name) {
            'Pet Boarding', 'House Sitting' => 'night',
            'Pet Daycare' => 'day',
            'Dog Walking' => 'walk',
            'Pet Taxi' => 'trip',
            default => 'session',
        };

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'tagline' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'icon' => '🐾',
            'pricing_unit' => $unit,
            'requires_date_range' => in_array($unit, ['night', 'day'], true),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
