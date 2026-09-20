<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceSearchTest extends TestCase
{
    use RefreshDatabase;

    private function provider(array $attributes = [], ?ServiceCategory $category = null): ProviderProfile
    {
        $profile = ProviderProfile::factory()
            ->forUser(User::factory()->create())
            ->create($attributes);

        ProviderService::factory()
            ->forCategory($category ?? ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $profile->id]);

        return $profile;
    }

    public function test_the_services_landing_page_lists_categories(): void
    {
        $this->seed(ServiceCategorySeeder::class);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Pet Boarding')
            ->assertSee('Dog Walking')
            ->assertSee('Pet Grooming');
    }

    public function test_a_category_page_shows_only_providers_offering_it(): void
    {
        $boarding = ServiceCategory::factory()->create(['name' => 'Pet Boarding', 'slug' => 'boarding', 'pricing_unit' => 'night']);
        $grooming = ServiceCategory::factory()->create(['name' => 'Pet Grooming', 'slug' => 'grooming', 'pricing_unit' => 'session']);

        $boarder = $this->provider([], $boarding);
        $groomer = $this->provider([], $grooming);

        $this->get(route('services.show', $boarding))
            ->assertOk()
            ->assertSee($boarder->user->name)
            ->assertDontSee($groomer->user->name);
    }

    public function test_selecting_multiple_categories_shows_providers_from_each(): void
    {
        $boarding = ServiceCategory::factory()->create(['name' => 'Pet Boarding', 'slug' => 'boarding', 'pricing_unit' => 'night']);
        $grooming = ServiceCategory::factory()->create(['name' => 'Pet Grooming', 'slug' => 'grooming', 'pricing_unit' => 'session']);
        $walking = ServiceCategory::factory()->create(['name' => 'Dog Walking', 'slug' => 'dog-walking', 'pricing_unit' => 'walk']);

        $boarder = $this->provider([], $boarding);
        $groomer = $this->provider([], $grooming);
        $walker = $this->provider([], $walking);

        $this->get(route('services.index', ['category' => ['boarding', 'grooming']]))
            ->assertOk()
            ->assertSee($boarder->user->name)
            ->assertSee($groomer->user->name)
            ->assertDontSee($walker->user->name);
    }

    public function test_the_any_service_search_shows_all_providers(): void
    {
        $boarder = $this->provider([], ServiceCategory::factory()->create(['slug' => 'boarding']));
        $groomer = $this->provider([], ServiceCategory::factory()->create(['slug' => 'grooming']));

        // An empty search submission ("Any service", no keyword or location).
        $this->get(route('services.index', ['q' => '', 'location' => '', 'category' => []]))
            ->assertOk()
            ->assertSee($boarder->user->name)
            ->assertSee($groomer->user->name);
    }

    public function test_location_search_matches_city_case_insensitively(): void
    {
        $pj = $this->provider(['city' => 'Petaling Jaya']);
        $jb = $this->provider(['city' => 'Johor Bahru']);

        $this->get(route('services.index', ['location' => 'petaling']))
            ->assertOk()
            ->assertSee($pj->user->name)
            ->assertDontSee($jb->user->name);
    }

    public function test_location_search_also_matches_postcode(): void
    {
        $match = $this->provider(['postcode' => '46000']);
        $other = $this->provider(['postcode' => '80100']);

        $this->get(route('services.index', ['location' => '46000']))
            ->assertOk()
            ->assertSee($match->user->name)
            ->assertDontSee($other->user->name);
    }

    public function test_keyword_search_matches_the_headline(): void
    {
        $match = $this->provider(['headline' => 'Gentle grooming for nervous cats']);
        $other = $this->provider(['headline' => 'Long countryside dog walks']);

        $this->get(route('services.index', ['q' => 'nervous']))
            ->assertOk()
            ->assertSee($match->user->name)
            ->assertDontSee($other->user->name);
    }

    public function test_keyword_search_reaches_into_the_services_listed(): void
    {
        $category = ServiceCategory::factory()->create();

        $profile = ProviderProfile::factory()->forUser(User::factory()->create())
            ->create(['headline' => 'Experienced sitter']);
        ProviderService::factory()->forCategory($category)->create([
            'provider_profile_id' => $profile->id,
            'description' => 'Includes hydrotherapy for older dogs.',
        ]);

        $other = $this->provider(['headline' => 'Experienced sitter too']);

        $this->get(route('services.index', ['q' => 'hydrotherapy']))
            ->assertOk()
            ->assertSee($profile->user->name)
            ->assertDontSee($other->user->name);
    }

    public function test_unpublished_and_unapproved_providers_are_hidden(): void
    {
        $live = $this->provider(['city' => 'Ipoh']);
        $draft = $this->provider(['city' => 'Ipoh', 'status' => 'draft', 'published_at' => null]);
        $suspended = $this->provider(['city' => 'Ipoh', 'status' => 'suspended']);

        $this->get(route('services.index', ['location' => 'Ipoh']))
            ->assertOk()
            ->assertSee($live->user->name)
            ->assertDontSee($draft->user->name)
            ->assertDontSee($suspended->user->name);
    }

    public function test_a_search_with_no_matches_shows_the_empty_state(): void
    {
        $this->provider(['city' => 'Penang']);

        $this->get(route('services.index', ['location' => 'Nowhere']))
            ->assertOk()
            ->assertSee('No providers matched that search');
    }

    public function test_the_public_profile_shows_services_and_rates(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Pet Boarding', 'pricing_unit' => 'night']);
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())->create();
        ProviderService::factory()->forCategory($category)->create([
            'provider_profile_id' => $profile->id,
            'price' => 65,
        ]);

        $this->get(route('providers.show', $profile))
            ->assertOk()
            ->assertSee($profile->user->name)
            ->assertSee($profile->headline)
            ->assertSee('RM 65')
            ->assertSee('per night');
    }

    public function test_a_draft_profile_is_not_publicly_viewable(): void
    {
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())
            ->draft()->create();

        $this->get(route('providers.show', $profile))->assertForbidden();

        $this->actingAs($profile->user)
            ->get(route('providers.show', $profile))
            ->assertOk();
    }
}
