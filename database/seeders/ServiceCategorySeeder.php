<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /** Idempotent — safe to re-run without wiping the table. */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pet Boarding',
                'slug' => 'boarding',
                'icon' => '🏠',
                'tagline' => 'Your pet stays overnight at the sitter’s home',
                'description' => 'Home boarding in a sitter’s own house — company, walks and cuddles instead of a kennel.',
                'pricing_unit' => 'night',
                'requires_date_range' => true,
            ],
            [
                'name' => 'House Sitting',
                'slug' => 'house-sitting',
                'icon' => '🛋️',
                'tagline' => 'A sitter stays over at your place',
                'description' => 'Your pet keeps its own bed, bowls and routine while a sitter stays at your home.',
                'pricing_unit' => 'night',
                'requires_date_range' => true,
            ],
            [
                'name' => 'Dog Walking',
                'slug' => 'dog-walking',
                'icon' => '🦮',
                'tagline' => 'Regular walks around your neighbourhood',
                'description' => 'A walker collects your dog for a proper stretch of the legs — solo or in a small group.',
                'pricing_unit' => 'walk',
                'requires_date_range' => false,
            ],
            [
                'name' => 'Pet Daycare',
                'slug' => 'daycare',
                'icon' => '☀️',
                'tagline' => 'Daytime care while you are at work',
                'description' => 'Drop off in the morning, collect in the evening. Company and play through the working day.',
                'pricing_unit' => 'day',
                'requires_date_range' => true,
            ],
            [
                'name' => 'Pet Grooming',
                'slug' => 'grooming',
                'icon' => '✂️',
                'tagline' => 'Baths, trims and nail clipping',
                'description' => 'Full grooming sessions at the groomer’s place or mobile, at your doorstep.',
                'pricing_unit' => 'session',
                'requires_date_range' => false,
            ],
            [
                'name' => 'Pet Taxi',
                'slug' => 'pet-taxi',
                'icon' => '🚗',
                'tagline' => 'Rides to the vet, groomer or airport',
                'description' => 'Door-to-door transport in a pet-friendly vehicle, for pets travelling without you.',
                'pricing_unit' => 'trip',
                'requires_date_range' => false,
            ],
            [
                'name' => 'Pet Training',
                'slug' => 'training',
                'icon' => '🎓',
                'tagline' => 'Obedience, puppy and behaviour sessions',
                'description' => 'One-to-one training covering basic obedience, socialisation and problem behaviours.',
                'pricing_unit' => 'session',
                'requires_date_range' => false,
            ],
        ];

        foreach ($categories as $index => $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'sort_order' => $index, 'is_active' => true],
            );
        }
    }
}
