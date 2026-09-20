<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Pet;
use App\Models\PetPhoto;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetListingTest extends TestCase
{
    use RefreshDatabase;

    private User $rescuer;

    private Species $species;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rescuer = User::factory()->create(['role' => 'adopter']);
        $this->species = Species::create(['name' => 'Cat', 'slug' => 'cat']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'Luna',
            'species_id' => $this->species->id,
            'description' => 'Found under a car in Bangsar, now thriving in foster care.',
            'age_group' => 'young',
            'gender' => 'female',
            'size' => 'small',
            'status' => 'available',
            ...$overrides,
        ];
    }

    public function test_the_listing_pages_render(): void
    {
        $pet = Pet::factory()->create([
            'listed_by' => $this->rescuer->id,
            'species_id' => $this->species->id,
        ]);

        $this->actingAs($this->rescuer)->get(route('listings.index'))->assertOk()->assertSee($pet->name);
        $this->actingAs($this->rescuer)->get(route('listings.create'))->assertOk();
        $this->actingAs($this->rescuer)->get(route('listings.edit', $pet))->assertOk();
    }

    public function test_any_signed_in_user_can_create_a_listing_without_a_shelter(): void
    {
        $response = $this->actingAs($this->rescuer)
            ->post(route('listings.store'), $this->validPayload());

        $pet = Pet::firstWhere('name', 'Luna');

        $response->assertRedirect(route('listings.edit', $pet));
        $this->assertNull($pet->organization_id, 'A rescuer should be able to list with no shelter.');
        $this->assertSame($this->rescuer->id, $pet->listed_by);
        $this->assertSame('draft', $pet->review_status);
        $this->assertNull($pet->published_at);
    }

    public function test_a_listing_can_name_a_shelter(): void
    {
        $org = Organization::factory()->create();

        $this->actingAs($this->rescuer)
            ->post(route('listings.store'), $this->validPayload(['organization_id' => $org->id]));

        $this->assertSame($org->id, Pet::firstWhere('name', 'Luna')->organization_id);
    }

    public function test_guests_cannot_create_listings(): void
    {
        $this->post(route('listings.store'), $this->validPayload())->assertRedirect(route('login'));
    }

    public function test_slugs_do_not_collide(): void
    {
        $this->actingAs($this->rescuer)->post(route('listings.store'), $this->validPayload());
        $this->actingAs($this->rescuer)->post(route('listings.store'), $this->validPayload());

        $this->assertSame(['luna', 'luna-2'], Pet::orderBy('id')->pluck('slug')->all());
    }

    public function test_a_lister_cannot_edit_someone_elses_listing(): void
    {
        $pet = Pet::factory()->create(['listed_by' => $this->rescuer->id, 'species_id' => $this->species->id]);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('listings.edit', $pet))->assertForbidden();
        $this->actingAs($stranger)
            ->put(route('listings.update', $pet), $this->validPayload())
            ->assertForbidden();
    }

    public function test_uploaded_photos_are_stored_and_the_first_is_primary(): void
    {
        Storage::fake('public');

        $this->actingAs($this->rescuer)->post(route('listings.store'), $this->validPayload([
            'photos' => [
                UploadedFile::fake()->image('one.jpg', 2400, 1800),
                UploadedFile::fake()->image('two.jpg', 800, 600),
            ],
        ]));

        $photos = Pet::firstWhere('name', 'Luna')->photos;

        $this->assertCount(2, $photos);
        $this->assertTrue($photos->first()->is_primary);
        $this->assertFalse($photos->last()->is_primary);

        foreach ($photos as $photo) {
            Storage::disk('public')->assertExists($photo->path);
        }
    }

    public function test_a_listing_needs_a_photo_before_review(): void
    {
        $pet = Pet::factory()->create([
            'listed_by' => $this->rescuer->id,
            'species_id' => $this->species->id,
            'review_status' => 'draft',
        ]);

        $this->actingAs($this->rescuer)->post(route('listings.submit', $pet));

        $this->assertSame('draft', $pet->fresh()->review_status);
    }

    public function test_submitting_sends_a_listing_to_the_queue(): void
    {
        $pet = Pet::factory()->create([
            'listed_by' => $this->rescuer->id,
            'species_id' => $this->species->id,
            'review_status' => 'draft',
        ]);
        PetPhoto::create(['pet_id' => $pet->id, 'path' => 'pets/x.jpg', 'is_primary' => true]);

        $this->actingAs($this->rescuer)->post(route('listings.submit', $pet));

        $this->assertSame('submitted', $pet->fresh()->review_status);
        $this->assertSame(1, Pet::awaitingReview()->count());
    }

    public function test_unpublished_listings_are_not_publicly_visible(): void
    {
        $pet = Pet::factory()->create([
            'listed_by' => $this->rescuer->id,
            'species_id' => $this->species->id,
            'review_status' => 'submitted',
            'published_at' => null,
        ]);

        // A draft leaks nothing, even to someone who guesses the slug.
        $this->get(route('pets.show', $pet))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('pets.show', $pet))->assertForbidden();

        // Its own lister and staff may preview it.
        $this->actingAs($this->rescuer)->get(route('pets.show', $pet))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->get(route('pets.show', $pet))->assertOk();
    }
}
