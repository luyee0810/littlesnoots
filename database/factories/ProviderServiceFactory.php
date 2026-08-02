<?php

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderService>
 */
class ProviderServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider_profile_id' => ProviderProfile::factory(),
            'service_category_id' => ServiceCategory::factory(),
            'title' => null,
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(30, 150),
            'price_unit' => 'session',
            'currency' => 'MYR',
            'additional_pet_price' => fake()->optional(0.6)->numberBetween(10, 40),
            'min_units' => 1,
            'max_pets' => fake()->numberBetween(1, 3),
            'is_active' => true,
        ];
    }

    /** Price and unit are driven by the category — keep them in step. */
    public function forCategory(ServiceCategory $category): static
    {
        // Ringgit, roughly matching Klang Valley market rates.
        $price = match ($category->pricing_unit) {
            'night' => fake()->numberBetween(35, 90),
            'day' => fake()->numberBetween(30, 70),
            'walk' => fake()->numberBetween(15, 35),
            'trip' => fake()->numberBetween(20, 60),
            'hour' => fake()->numberBetween(30, 70),
            default => fake()->numberBetween(50, 150),
        };

        return $this->state(fn () => [
            'service_category_id' => $category->id,
            'price_unit' => $category->pricing_unit,
            'price' => $price,
        ]);
    }
}
