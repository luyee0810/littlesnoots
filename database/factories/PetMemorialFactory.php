<?php

namespace Database\Factories;

use App\Models\PetMemorial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PetMemorial>
 */
class PetMemorialFactory extends Factory
{
    protected $model = PetMemorial::class;

    public function definition(): array
    {
        $name = fake()->firstName();
        $born = fake()->dateTimeBetween('-18 years', '-4 years');
        $passed = fake()->dateTimeBetween($born, 'now');

        return [
            'user_id' => User::factory(),
            'pet_name' => $name,
            'species' => fake()->randomElement(['Cat', 'Dog', 'Golden Retriever', 'Kucing Kampung', 'Rabbit']),
            'photo_path' => null,
            'born_on' => $born,
            'passed_on' => $passed,
            'tribute' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        ];
    }
}
