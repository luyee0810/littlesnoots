<?php

namespace Tests\Feature;

use App\Models\Breed;
use App\Models\Pet;
use App\Models\ProviderProfile;
use App\Models\ServiceCategory;
use App\Models\Species;
use App\Models\User;
use Database\Seeders\ReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_taxonomy_and_service_categories(): void
    {
        $this->seed(ReferenceSeeder::class);

        $this->assertSame(3, Species::count());
        $this->assertGreaterThan(20, Breed::count());
        $this->assertSame(7, ServiceCategory::count());

        // Every breed belongs to a species — a pet can always resolve its label.
        $this->assertSame(0, Breed::doesntHave('species')->count());
    }

    public function test_it_creates_no_demo_content(): void
    {
        $this->seed(ReferenceSeeder::class);

        // Safe to run on production: reference rows only, no fake pets or accounts.
        $this->assertSame(0, Pet::count());
        $this->assertSame(0, User::count());
        $this->assertSame(0, ProviderProfile::count());
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(ReferenceSeeder::class);

        $species = Species::count();
        $breeds = Breed::count();
        $categories = ServiceCategory::count();

        $this->seed(ReferenceSeeder::class);

        $this->assertSame($species, Species::count());
        $this->assertSame($breeds, Breed::count());
        $this->assertSame($categories, ServiceCategory::count());
    }
}
