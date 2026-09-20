<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetPhoto;
use App\Models\ProviderProfile;
use App\Models\Species;
use App\Models\User;
use App\Notifications\AdoptionApplicationReceived;
use App\Notifications\PetListingApproved;
use App\Notifications\PetListingNeedsChanges;
use App\Notifications\ProviderProfileApproved;
use App\Notifications\ProviderProfileSuspended;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $rescuer;

    private Pet $pet;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->rescuer = User::factory()->create();

        $this->pet = Pet::factory()->create([
            'species_id' => Species::create(['name' => 'Cat', 'slug' => 'cat'])->id,
            'listed_by' => $this->rescuer->id,
            'review_status' => 'submitted',
            'published_at' => null,
            'status' => 'available',
        ]);

        PetPhoto::create(['pet_id' => $this->pet->id, 'path' => 'pets/x.jpg', 'is_primary' => true]);
    }

    public function test_the_lister_hears_when_their_listing_is_approved(): void
    {
        $this->actingAs($this->staff)->patch(route('admin.pets.approve', $this->pet));

        Notification::assertSentTo($this->rescuer, PetListingApproved::class);
    }

    public function test_the_lister_hears_why_a_listing_was_sent_back(): void
    {
        $this->actingAs($this->staff)->patch(route('admin.pets.reject', $this->pet), [
            'review_notes' => 'The photos are too dark to see the cat.',
        ]);

        Notification::assertSentTo(
            $this->rescuer,
            PetListingNeedsChanges::class,
            function (PetListingNeedsChanges $notification) {
                $mail = $notification->toMail($this->rescuer);

                // The reason has to reach the lister, not just the database.
                return in_array('The photos are too dark to see the cat.', $mail->introLines, true);
            },
        );
    }

    public function test_the_lister_hears_about_a_new_adoption_application(): void
    {
        $this->pet->markApproved($this->staff);

        $this->post(route('pets.apply', $this->pet), [
            'applicant_name' => 'Siti Rahman',
            'applicant_email' => 'siti@example.com',
            'applicant_phone' => '012-345 6789',
            'message' => 'I have a quiet flat and work from home.',
        ]);

        Notification::assertSentTo($this->rescuer, AdoptionApplicationReceived::class);
    }

    public function test_a_sitter_hears_about_approval_and_suspension(): void
    {
        $provider = ProviderProfile::factory()->pendingApproval()->create();

        $this->actingAs($this->staff)->patch(route('admin.providers.approve', $provider));
        Notification::assertSentTo($provider->user, ProviderProfileApproved::class);

        $this->actingAs($this->staff)->patch(route('admin.providers.suspend', $provider), [
            'review_notes' => 'Please add a photo of the room pets would stay in.',
        ]);
        Notification::assertSentTo($provider->user, ProviderProfileSuspended::class);
    }

    public function test_nobody_is_notified_when_a_decision_is_rejected_by_validation(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('admin.pets.reject', $this->pet), ['review_notes' => 'no']);

        Notification::assertNothingSent();
    }
}
