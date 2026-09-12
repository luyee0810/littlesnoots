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
            'email' => 'admin@littlesnoots.test',
            'role' => 'admin',
        ]);

        $staff = User::factory()->create([
            'name' => 'Shelter Staff',
            'email' => 'staff@littlesnoots.test',
            'role' => 'staff',
        ]);

        // ---- Organizations (shelters / rescues) --------------------
        $secondChance = Organization::create([
            'name' => 'Second Chance Animal Shelter',
            'slug' => 'second-chance-animal-shelter',
            'type' => 'shelter',
            'email' => 'hello@secondchance.test',
            'phone' => '03-2145 6789',
            'website' => 'https://secondchance.test',
            'address1' => '12, Jalan Kenari 5, Bandar Puchong Jaya',
            'city' => 'Puchong',
            'state' => 'Selangor',
            'postcode' => '47100',
            'country' => 'MY',
            'mission_statement' => 'Second Chance Animal Shelter is a no-kill shelter in the Klang Valley, rehoming abandoned and surrendered pets across Selangor and Kuala Lumpur.',
            'adoption_policy' => 'Adopters must be 18+, send an enquiry, and agree to a short home visit. Fees cover vaccinations, deworming and spay/neuter.',
            'hours' => [
                'mon' => '10:00–18:00', 'tue' => '10:00–18:00', 'wed' => '10:00–18:00',
                'thu' => '10:00–18:00', 'fri' => '10:00–18:00', 'sat' => '10:00–16:00', 'sun' => 'Closed',
            ],
            'facebook' => 'https://facebook.com/secondchancemy',
            'instagram' => 'https://instagram.com/secondchancemy',
        ]);

        $organizations = collect([$secondChance])->merge(Organization::factory(2)->create());

        // ---- Species + breeds --------------------------------------
        $taxonomy = [
            // Breeds you actually see in Malaysian shelters — local mixes dominate.
            'Cat' => ['Kucing Kampung', 'Domestic Shorthair', 'Persian Mix', 'Siamese', 'Maine Coon'],
            'Dog' => ['Kampung Dog', 'Golden Retriever', 'Shih Tzu', 'Poodle', 'Beagle'],
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

                    // Two photos per pet, drawn from the bundled library in
                    // `public/images/seed` so every listing shows the right animal.
                    foreach ([true, false] as $i => $primary) {
                        PetPhoto::create([
                            'pet_id' => $pet->id,
                            'path' => self::photoPath($speciesName, $pet->id * 2 + $i),
                            'alt' => "Photo of {$pet->name}",
                            'is_primary' => $primary,
                            'sort_order' => $i,
                        ]);
                    }
                });
        }

        // ---- Phase 2 — services marketplace -------------------------
        $this->call([
            ServiceCategorySeeder::class,
            ServiceDemoSeeder::class,
            MemorialDemoSeeder::class,
        ]);
    }

    /**
     * A bundled demo photo for the species, picked deterministically so the
     * same pet keeps the same picture across reseeds.
     */
    public static function photoPath(string $speciesName, int $n): string
    {
        $folder = strtolower($speciesName).'s';           // cats / dogs / rabbits
        $prefix = strtolower($speciesName);               // cat / dog / rabbit
        $count = count(glob(public_path("images/seed/{$folder}/*.jpg"))) ?: 1;
        $index = $n % $count + 1;

        return sprintf('/images/seed/%s/%s-%02d.jpg', $folder, $prefix, $index);
    }
}
