<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Pet;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    public function test_shelters_are_staff_only(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.organizations.index'))
            ->assertForbidden();
    }

    public function test_staff_can_add_a_shelter(): void
    {
        $this->actingAs($this->staff)->post(route('admin.organizations.store'), [
            'name' => 'Paws Rescue KL',
            'type' => 'rescue',
            'country' => 'MY',
            'city' => 'Kuala Lumpur',
            'adoption_policy' => 'Home visit required.',
            'hours' => ['mon' => '10:00–18:00', 'tue' => '', 'sun' => 'Closed'],
        ]);

        $organization = Organization::firstWhere('name', 'Paws Rescue KL');

        $this->assertSame('paws-rescue-kl', $organization->slug);
        // Blank days are dropped rather than stored as empty strings.
        $this->assertSame(['mon' => '10:00–18:00', 'sun' => 'Closed'], $organization->hours);
    }

    public function test_deleting_a_shelter_keeps_its_listings(): void
    {
        $organization = Organization::factory()->create();

        $pet = Pet::factory()->create([
            'species_id' => Species::create(['name' => 'Cat', 'slug' => 'cat'])->id,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($this->staff)->delete(route('admin.organizations.destroy', $organization));

        $this->assertModelExists($pet);
        $this->assertNull($pet->fresh()->organization_id);
    }

    // ---- Impersonation -------------------------------------------------

    public function test_an_ordinary_member_cannot_attach_a_shelter_to_their_listing(): void
    {
        $organization = Organization::factory()->create();
        $rescuer = User::factory()->create();
        $species = Species::create(['name' => 'Cat', 'slug' => 'cat']);

        $this->actingAs($rescuer)->post(route('listings.store'), [
            'name' => 'Luna',
            'species_id' => $species->id,
            'description' => 'Found under a car in Bangsar.',
            'age_group' => 'young',
            'gender' => 'female',
            'size' => 'small',
            'status' => 'available',
            'organization_id' => $organization->id,   // claiming a real rescue
        ]);

        $pet = Pet::firstWhere('name', 'Luna');

        $this->assertNotNull($pet, 'The listing is still created.');
        $this->assertNull($pet->organization_id, 'A member must not be able to claim a shelter.');
    }

    public function test_the_shelter_picker_is_not_offered_to_members(): void
    {
        Organization::factory()->create(['name' => 'Second Chance Shelter']);

        $this->actingAs(User::factory()->create())
            ->get(route('listings.create'))
            ->assertOk()
            ->assertDontSee('Second Chance Shelter');
    }

    public function test_staff_can_still_attach_a_shelter(): void
    {
        $organization = Organization::factory()->create();
        $species = Species::create(['name' => 'Cat', 'slug' => 'cat']);

        $this->actingAs($this->staff)->post(route('listings.store'), [
            'name' => 'Mochi',
            'species_id' => $species->id,
            'description' => 'Surrendered by a family moving overseas.',
            'age_group' => 'adult',
            'gender' => 'male',
            'size' => 'medium',
            'status' => 'available',
            'organization_id' => $organization->id,
        ]);

        $this->assertSame($organization->id, Pet::firstWhere('name', 'Mochi')->organization_id);
    }
}
