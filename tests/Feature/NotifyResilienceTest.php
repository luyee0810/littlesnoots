<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\Species;
use App\Models\User;
use App\Notifications\PetListingApproved;
use App\Support\Notify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyResilienceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mail is sent during the request, so an SMTP outage must not turn a
     * successful approval into a 500 — the listing is already published.
     */
    public function test_a_mail_failure_does_not_break_the_action(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $rescuer = User::factory()->create();

        $pet = Pet::factory()->create([
            'species_id' => Species::create(['name' => 'Cat', 'slug' => 'cat'])->id,
            'listed_by' => $rescuer->id,
            'review_status' => 'submitted',
            'published_at' => null,
        ]);

        // Every send throws, as a dead mail server would.
        Notification::shouldReceive('send')
            ->andThrow(new \RuntimeException('Connection could not be established'));

        Log::shouldReceive('warning')->once();

        $this->actingAs($staff)
            ->patch(route('admin.pets.approve', $pet))
            ->assertRedirect(route('admin.pets.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $pet->fresh()->review_status);
        $this->assertNotNull($pet->fresh()->published_at);
    }

    public function test_a_missing_recipient_sends_nothing(): void
    {
        Notification::fake();

        // Seeded pets can have no lister at all (listed_by is nullable).
        Notify::send(null, new PetListingApproved(new Pet));

        Notification::assertNothingSent();
    }
}
