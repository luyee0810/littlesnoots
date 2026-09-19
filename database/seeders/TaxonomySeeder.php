<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Species;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Species and breeds — reference data, not demo content.
 *
 * Every pet listing needs a species and (usually) a breed, so these rows must
 * exist on a real installation before anyone can list an animal. Idempotent:
 * safe to re-run on production as the list grows.
 */
class TaxonomySeeder extends Seeder
{
    /** Breeds you actually see in Malaysian shelters — local mixes dominate. */
    public const TAXONOMY = [
        'Cat' => [
            'Kucing Kampung', 'Domestic Shorthair', 'Domestic Longhair', 'Persian Mix',
            'Siamese', 'Maine Coon', 'British Shorthair', 'Bengal', 'Ragdoll',
            'Scottish Fold', 'Munchkin', 'Calico', 'Tabby', 'Tuxedo',
        ],
        'Dog' => [
            'Kampung Dog', 'Golden Retriever', 'Labrador Retriever', 'Shih Tzu',
            'Poodle', 'Beagle', 'Pomeranian', 'Siberian Husky', 'German Shepherd',
            'Chihuahua', 'Corgi', 'Border Collie', 'Jack Russell Terrier',
            'Rottweiler', 'Terrier Mix',
        ],
        'Rabbit' => [
            'Holland Lop', 'Netherland Dwarf', 'Lionhead', 'Rex', 'Flemish Giant',
            'Mini Lop',
        ],
    ];

    public function run(): void
    {
        foreach (self::TAXONOMY as $speciesName => $breeds) {
            $species = Species::firstOrCreate(
                ['slug' => Str::slug($speciesName)],
                ['name' => $speciesName],
            );

            foreach ($breeds as $breedName) {
                Breed::firstOrCreate(
                    ['species_id' => $species->id, 'slug' => Str::slug($breedName)],
                    ['name' => $breedName],
                );
            }
        }
    }
}
