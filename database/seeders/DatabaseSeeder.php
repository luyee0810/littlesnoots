<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Organization;
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

        // ---- Organizations (shelters / rescues) --------------------
        $twoFatCats = Organization::create([
            'name' => 'Two Fat Cats Shelter',
            'slug' => 'two-fat-cats-shelter',
            'type' => 'shelter',
            'email' => 'hello@twofatcats.test',
            'phone' => '(555) 012-3456',
            'website' => 'https://twofatcats.test',
            'address1' => '12 Whisker Lane',
            'city' => 'Portland',
            'state' => 'ME',
            'postcode' => '04101',
            'country' => 'US',
            'mission_statement' => 'Two Fat Cats is a no-kill shelter dedicated to rehoming abandoned and surrendered pets across New England.',
            'adoption_policy' => 'Adopters must be 18+, complete an application, and pass a brief home check. Fees cover vaccinations and spay/neuter.',
            'hours' => [
                'mon' => '10:00–17:00', 'tue' => '10:00–17:00', 'wed' => '10:00–17:00',
                'thu' => '10:00–17:00', 'fri' => '10:00–17:00', 'sat' => '10:00–15:00', 'sun' => 'Closed',
            ],
            'facebook' => 'https://facebook.com/twofatcats',
            'instagram' => 'https://instagram.com/twofatcats',
        ]);

        $organizations = collect([$twoFatCats])->merge(Organization::factory(2)->create());

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
                    'organization_id' => $organizations->random()->id,
                    // Cats can't be "good with cats" flagged oddly; keep declawed only for cats
                    'declawed' => $speciesName === 'Cat' ? fake()->boolean(15) : false,
                ])
                ->each(function (Pet $pet) use ($breedPool, $speciesName, $organizations) {
                    $breeds = collect($breedPool[$speciesName]);

                    $pet->update([
                        'organization_id' => $organizations->random()->id,
                        'breed_id' => $breeds->random()->id,
                        'secondary_breed_id' => $pet->breed_mixed ? $breeds->random()->id : null,
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
