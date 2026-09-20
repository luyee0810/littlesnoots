<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetPhoto;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $rescuer;

    private Pet $pet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->rescuer = User::factory()->create(['role' => 'adopter']);

        $species = Species::create(['name' => 'Cat', 'slug' => 'cat']);

        $this->pet = Pet::factory()->create([
            'species_id' => $species->id,
            'listed_by' => $this->rescuer->id,
            'organization_id' => null,
            'review_status' => 'submitted',
            'published_at' => null,
        ]);

        PetPhoto::create(['pet_id' => $this->pet->id, 'path' => 'pets/x.jpg', 'is_primary' => true]);
    }

    public function test_the_admin_area_is_closed_to_non_staff(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($this->rescuer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->rescuer)->get(route('admin.pets.index'))->assertForbidden();
        $this->actingAs($this->rescuer)
            ->patch(route('admin.pets.approve', $this->pet))
            ->assertForbidden();
    }

    public function test_staff_see_the_queue(): void
    {
        $this->actingAs($this->staff)
            ->get(route('admin.pets.index'))
            ->assertOk()
            ->assertSee($this->pet->name);
    }

    public function test_the_admin_pages_render(): void
    {
        $this->actingAs($this->staff)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Listings awaiting review');

        $this->actingAs($this->staff)
            ->get(route('admin.pets.show', $this->pet))
            ->assertOk()
            ->assertSee($this->pet->description);
    }

    public function test_approving_publishes_the_listing(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('admin.pets.approve', $this->pet))
            ->assertRedirect(route('admin.pets.index'));

        $pet = $this->pet->fresh();

        $this->assertSame('approved', $pet->review_status);
        $this->assertNotNull($pet->published_at);
        $this->assertSame($this->staff->id, $pet->reviewed_by);

        // Now public: visible to a guest, and listed on the adoption page.
        $this->get(route('pets.show', $pet))->assertOk();
        $this->get(route('pets.index'))->assertSee($pet->name);
    }

    public function test_rejecting_records_notes_and_keeps_it_unpublished(): void
    {
        $this->actingAs($this->staff)->patch(route('admin.pets.reject', $this->pet), [
            'review_notes' => 'The photos are too dark to see the cat — could you add a couple in daylight?',
        ]);

        $pet = $this->pet->fresh();

        $this->assertSame('rejected', $pet->review_status);
        $this->assertNull($pet->published_at);
        $this->assertStringContainsString('too dark', $pet->review_notes);

        // actingAs persists, so drop back to a guest before checking visibility.
        auth()->logout();
        $this->get(route('pets.show', $pet))->assertForbidden();
    }

    public function test_rejection_requires_a_reason(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('admin.pets.reject', $this->pet), ['review_notes' => 'no'])
            ->assertSessionHasErrors('review_notes');

        $this->assertSame('submitted', $this->pet->fresh()->review_status);
    }

    public function test_the_lister_sees_the_rejection_notes(): void
    {
        $this->actingAs($this->staff)->patch(route('admin.pets.reject', $this->pet), [
            'review_notes' => 'Please add a daylight photo of her face.',
        ]);

        $this->actingAs($this->rescuer)
            ->get(route('listings.edit', $this->pet))
            ->assertOk()
            ->assertSee('Please add a daylight photo of her face.');
    }

    public function test_a_live_listing_can_be_unpublished(): void
    {
        $this->pet->markApproved($this->staff);

        $this->actingAs($this->staff)->patch(route('admin.pets.unpublish', $this->pet), [
            'review_notes' => 'Taken down at the lister’s request — the cat has been adopted.',
        ]);

        $this->assertNull($this->pet->fresh()->published_at);
        $this->assertSame(0, Pet::published()->count());

        auth()->logout();
        $this->get(route('pets.show', $this->pet))->assertForbidden();
    }

    public function test_a_resubmitted_listing_clears_its_old_notes(): void
    {
        $this->pet->markRejected($this->staff, 'Photos are too dark to see anything.');

        $this->actingAs($this->rescuer)->post(route('listings.submit', $this->pet));

        $pet = $this->pet->fresh();

        $this->assertSame('submitted', $pet->review_status);
        $this->assertNull($pet->review_notes);
    }
}
