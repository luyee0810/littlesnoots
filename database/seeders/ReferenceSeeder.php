<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Everything a real installation needs, and nothing it doesn't.
 *
 * Production is never seeded with demo content, but the site cannot function
 * with empty species, breed and service_category tables — a pet listing has
 * nowhere to point. Run on production with:
 *
 *     php artisan db:seed --class=ReferenceSeeder --force
 *
 * Idempotent, so re-running it after adding new breeds is safe.
 */
class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TaxonomySeeder::class,
            ServiceCategorySeeder::class,
        ]);
    }
}
