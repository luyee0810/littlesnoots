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
    /** Malaysian shelters and rescues — the market this demo data is for. */
    private const NAMES = [
        'Kuala Lumpur Paws Sanctuary',
        'SPCA Selangor Rescue',
        'Furry Friends Klang Valley',
        'Second Chance Animal Shelter PJ',
        'Penang Island Animal Rescue',
        'Johor Bahru Pet Haven',
        'Nine Lives Rescue Cheras',
    ];

    /** [city, state, postcode prefix] */
    private const LOCATIONS = [
        ['Kuala Lumpur', 'Kuala Lumpur', '50'],
        ['Cheras', 'Kuala Lumpur', '56'],
        ['Petaling Jaya', 'Selangor', '46'],
        ['Subang Jaya', 'Selangor', '47'],
        ['Shah Alam', 'Selangor', '40'],
        ['Klang', 'Selangor', '41'],
        ['George Town', 'Pulau Pinang', '10'],
        ['Johor Bahru', 'Johor', '80'],
        ['Ipoh', 'Perak', '30'],
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(self::NAMES);
        [$city, $state, $postPrefix] = fake()->randomElement(self::LOCATIONS);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'type' => fake()->randomElement(['shelter', 'rescue', 'foster']),
            'email' => 'hello@'.Str::slug($name).'.my',
            'phone' => fake()->randomElement(['03-', '04-', '07-', '05-']).fake()->numerify('#### ####'),
            'website' => 'https://'.Str::slug($name).'.my',
            'address1' => fake()->buildingNumber().', Jalan '.fake()->randomElement([
                'Kenari', 'Bukit Bintang', 'Ampang', 'Damai', 'Sri Hartamas', 'Tun Razak', 'Bunga Raya',
            ]),
            'city' => $city,
            'state' => $state,
            'postcode' => $postPrefix.fake()->numerify('###'),
            'country' => 'MY',
            'mission_statement' => 'A volunteer-run rescue in '.$city.', rehoming strays and surrendered pets across '.$state.'.',
            'adoption_policy' => 'Adopters must be 18+, complete an enquiry form, and agree to a short home visit. Adoption fees cover vaccinations, deworming and spay/neuter.',
            'hours' => [
                'mon' => '10:00–18:00', 'tue' => '10:00–18:00', 'wed' => '10:00–18:00',
                'thu' => '10:00–18:00', 'fri' => '10:00–18:00', 'sat' => '10:00–16:00', 'sun' => 'Closed',
            ],
            'facebook' => 'https://facebook.com/'.Str::slug($name),
            'instagram' => 'https://instagram.com/'.Str::slug($name),
        ];
    }
}
