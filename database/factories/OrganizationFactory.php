<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company().' '.fake()->randomElement(['Animal Shelter', 'Pet Rescue', 'Humane Society']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'type' => fake()->randomElement(['shelter', 'rescue', 'foster']),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'address1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postcode' => fake()->postcode(),
            'country' => 'US',
            'mission_statement' => fake()->paragraph(),
            'adoption_policy' => 'All adopters must be 18+, complete an application, and pass a brief home check. Adoption fees cover vaccinations and spay/neuter.',
            'hours' => [
                'mon' => '10:00–17:00', 'tue' => '10:00–17:00', 'wed' => '10:00–17:00',
                'thu' => '10:00–17:00', 'fri' => '10:00–17:00', 'sat' => '10:00–15:00', 'sun' => 'Closed',
            ],
            'facebook' => 'https://facebook.com/'.Str::slug($name),
            'instagram' => 'https://instagram.com/'.Str::slug($name),
        ];
    }
}
