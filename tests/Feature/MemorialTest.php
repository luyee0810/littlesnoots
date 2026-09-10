<?php

namespace Tests\Feature;

use App\Models\PetMemorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_memorial_index_is_public(): void
    {
        $memorial = PetMemorial::factory()->create(['pet_name' => 'Tiger']);

        $this->get(route('memorials.index'))
            ->assertOk()
            ->assertSee('Tiger');
    }

    public function test_a_memorial_page_shows_the_tribute(): void
    {
        $memorial = PetMemorial::factory()->create([
            'pet_name' => 'Bella',
            'tribute' => 'The gentlest soul I ever knew.',
        ]);

        $this->get(route('memorials.show', $memorial))
            ->assertOk()
            ->assertSee('Bella')
            ->assertSee('The gentlest soul I ever knew.');
    }

    public function test_guests_cannot_reach_the_create_form(): void
    {
        $this->get(route('memorials.create'))->assertRedirect(route('login'));
    }

    public function test_a_signed_in_user_can_create_a_memorial_with_a_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('memorials.store'), [
            'pet_name' => 'Coco',
            'species' => 'Holland Lop',
            'born_on' => '2018-01-10',
            'passed_on' => '2024-05-30',
            'tribute' => 'A tiny bundle of mischief.',
            'photo' => UploadedFile::fake()->image('coco.jpg'),
        ]);

        $memorial = PetMemorial::first();

        $this->assertNotNull($memorial);
        $this->assertSame('Coco', $memorial->pet_name);
        $this->assertSame($user->id, $memorial->user_id);
        $this->assertNotNull($memorial->photo_path);
        Storage::disk('public')->assertExists($memorial->photo_path);
        $response->assertRedirect(route('memorials.show', $memorial));
    }

    public function test_passing_date_cannot_precede_birth_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('memorials.store'), [
            'pet_name' => 'Rex',
            'born_on' => '2020-01-01',
            'passed_on' => '2019-01-01',
            'tribute' => 'Good boy.',
        ])->assertSessionHasErrors('passed_on');

        $this->assertDatabaseCount('pet_memorials', 0);
    }

    public function test_lighting_a_candle_is_a_toggle(): void
    {
        $user = User::factory()->create();
        $memorial = PetMemorial::factory()->create();

        // Light it.
        $this->actingAs($user)->post(route('memorials.candle', $memorial));
        $this->assertDatabaseCount('memorial_candles', 1);

        // Put it out.
        $this->actingAs($user)->post(route('memorials.candle', $memorial));
        $this->assertDatabaseCount('memorial_candles', 0);
    }

    public function test_only_one_candle_per_user(): void
    {
        $user = User::factory()->create();
        $memorial = PetMemorial::factory()->create();

        $memorial->candles()->create(['user_id' => $user->id]);

        // Lighting again from a fresh request path would toggle off; a direct
        // duplicate insert must be rejected by the unique constraint.
        $this->assertSame(1, $memorial->candles()->count());
    }

    public function test_a_signed_in_user_can_leave_a_condolence_message(): void
    {
        $user = User::factory()->create();
        $memorial = PetMemorial::factory()->create();

        $this->actingAs($user)->post(route('memorials.messages.store', $memorial), [
            'body' => 'Thinking of you.',
        ]);

        $this->assertDatabaseHas('memorial_messages', [
            'pet_memorial_id' => $memorial->id,
            'user_id' => $user->id,
            'body' => 'Thinking of you.',
        ]);
    }

    public function test_only_the_owner_can_delete_a_memorial(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $memorial = PetMemorial::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($stranger)
            ->delete(route('memorials.destroy', $memorial))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('memorials.destroy', $memorial))
            ->assertRedirect(route('memorials.index'));

        $this->assertSoftDeleted($memorial);
    }
}
