<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Pet;
use App\Models\PetPhoto;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ---- Users --------------------------------------------------
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@twofatcats.test',
            'role' => 'admin',
        ]);

        $staff = User::factory()->create([
            'name' => 'Shelter Staff',
            'email' => 'staff@twofatcats.test',
            'role' => 'staff',
        ]);

        // ---- Species + breeds --------------------------------------
        $taxonomy = [
            'Cat' => ['Domestic Shorthair', 'Maine Coon', 'Siamese', 'Persian', 'Tabby'],
            'Dog' => ['Labrador Retriever', 'Beagle', 'Poodle', 'German Shepherd', 'Mixed'],
            'Rabbit' => ['Holland Lop', 'Netherland Dwarf'],
        ];

        $breedPool = [];

        foreach ($taxonomy as $speciesName => $breeds) {
            $species = Species::create([
                'name' => $speciesName,
                'slug' => Str::slug($speciesName),
            ]);

            foreach ($breeds as $breedName) {
                $breedPool[$speciesName][] = Breed::create([
                    'species_id' => $species->id,
                    'name' => $breedName,
                    'slug' => Str::slug($breedName),
                ]);
            }
        }

        // ---- Pets ---------------------------------------------------
        $species = Species::all()->keyBy('name');

        foreach (['Cat', 'Dog', 'Rabbit'] as $speciesName) {
            $count = $speciesName === 'Rabbit' ? 3 : 8;

            Pet::factory($count)
                ->for($species[$speciesName])
                ->create([
                    'listed_by' => $staff->id,
                ])
                ->each(function (Pet $pet) use ($breedPool, $speciesName) {
                    $pet->update([
                        'breed_id' => fake()->randomElement($breedPool[$speciesName])->id,
                    ]);

                    // Two placeholder photos per pet (seeded by pet id for stability).
                    foreach ([true, false] as $i => $primary) {
                        PetPhoto::create([
                            'pet_id' => $pet->id,
                            'path' => "https://picsum.photos/seed/pet{$pet->id}-{$i}/800/600",
                            'alt' => "Photo of {$pet->name}",
                            'is_primary' => $primary,
                            'sort_order' => $i,
                        ]);
                    }
                });
        }
    }
}
