<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Models\Species;
use App\Models\User;
use App\Notifications\AdoptionApplicationDecided;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class AdoptionApplicationReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $rescuer;

    private User $applicant;

    private Pet $pet;

    private AdoptionApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->rescuer = User::factory()->create();
        $this->applicant = User::factory()->create();

        $this->pet = Pet::factory()->create([
            'species_id' => Species::create(['name' => 'Cat', 'slug' => 'cat'])->id,
            'listed_by' => $this->rescuer->id,
            'status' => 'available',
            'review_status' => 'approved',
        ]);

        $this->application = $this->pet->applications()->create([
            'user_id' => $this->applicant->id,
            'applicant_name' => 'Siti Rahman',
            'applicant_email' => 'siti@example.com',
            'applicant_phone' => '012-345 6789',
            'message' => 'I work from home and have a quiet flat.',
            'status' => 'pending',
        ]);
    }

    public function test_the_lister_sees_their_applicants(): void
    {
        $this->actingAs($this->rescuer)
            ->get(route('listings.applications', $this->pet))
            ->assertOk()
            ->assertSee('Siti Rahman')
            ->assertSee('siti@example.com');
    }

    public function test_strangers_cannot_read_applicant_details(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('listings.applications', $this->pet))
            ->assertForbidden();

        // Not even the applicant sees the reviewing screen for the pet.
        $this->actingAs($this->applicant)
            ->get(route('listings.applications', $this->pet))
            ->assertForbidden();
    }

    public function test_staff_can_review_on_a_rescuers_behalf(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('listings.applications', $this->pet))
            ->assertOk();

        $this->actingAs($staff)->patch(route('applications.update', $this->application), [
            'decision' => 'approved',
        ]);

        $this->assertSame('approved', $this->application->fresh()->status);
        $this->assertSame($staff->id, $this->application->fresh()->reviewed_by);
    }

    public function test_an_applicant_cannot_decide_their_own_application(): void
    {
        $this->actingAs($this->applicant)
            ->patch(route('applications.update', $this->application), ['decision' => 'approved'])
            ->assertForbidden();

        $this->assertSame('pending', $this->application->fresh()->status);
    }

    public function test_marking_as_considering_does_not_email_the_applicant(): void
    {
        $this->actingAs($this->rescuer)->patch(route('applications.update', $this->application), [
            'decision' => 'reviewing',
            'staff_notes' => 'Sounds promising, arranging a call.',
        ]);

        $application = $this->application->fresh();

        $this->assertSame('reviewing', $application->status);
        $this->assertSame('Sounds promising, arranging a call.', $application->staff_notes);

        // Nothing is settled yet, so there is nothing to tell them.
        Notification::assertNothingSent();
    }

    public function test_approving_emails_the_applicant_and_can_move_the_pet(): void
    {
        $this->actingAs($this->rescuer)->patch(route('applications.update', $this->application), [
            'decision' => 'approved',
            'pet_status' => 'pending',
        ]);

        $this->assertSame('approved', $this->application->fresh()->status);
        $this->assertSame('pending', $this->pet->fresh()->status);

        Notification::assertSentTo($this->applicant, AdoptionApplicationDecided::class);
    }

    public function test_the_pet_is_left_alone_unless_asked(): void
    {
        $this->actingAs($this->rescuer)->patch(route('applications.update', $this->application), [
            'decision' => 'approved',
        ]);

        $this->assertSame('available', $this->pet->fresh()->status);
    }

    public function test_rejecting_emails_the_applicant(): void
    {
        $this->actingAs($this->rescuer)->patch(route('applications.update', $this->application), [
            'decision' => 'rejected',
            'staff_notes' => 'We’ve found a home closer to the shelter.',
        ]);

        $this->assertSame('rejected', $this->application->fresh()->status);
        Notification::assertSentTo($this->applicant, AdoptionApplicationDecided::class);
    }

    public function test_a_decided_application_cannot_be_decided_again(): void
    {
        $this->application->markApproved($this->rescuer);

        $this->actingAs($this->rescuer)
            ->patch(route('applications.update', $this->application), ['decision' => 'rejected'])
            ->assertSessionHas('error');

        $this->assertSame('approved', $this->application->fresh()->status);
    }

    public function test_illegal_transitions_throw_rather_than_corrupt_the_record(): void
    {
        $this->application->markApproved($this->rescuer);

        $this->expectException(RuntimeException::class);

        $this->application->markRejected($this->rescuer);
    }

    public function test_an_applicant_can_withdraw_while_it_is_open(): void
    {
        $this->actingAs($this->applicant)
            ->patch(route('applications.withdraw', $this->application));

        $this->assertSame('withdrawn', $this->application->fresh()->status);
    }

    public function test_nobody_else_can_withdraw_an_application(): void
    {
        $this->actingAs($this->rescuer)
            ->patch(route('applications.withdraw', $this->application))
            ->assertForbidden();

        $this->assertSame('pending', $this->application->fresh()->status);
    }

    public function test_a_guest_applicant_is_emailed_at_the_address_they_left(): void
    {
        $guestApplication = $this->pet->applications()->create([
            'user_id' => null,
            'applicant_name' => 'Wei Ming',
            'applicant_email' => 'weiming@example.com',
            'status' => 'pending',
        ]);

        $this->actingAs($this->rescuer)->patch(route('applications.update', $guestApplication), [
            'decision' => 'approved',
        ]);

        Notification::assertSentOnDemand(AdoptionApplicationDecided::class);
    }

    public function test_the_admin_overview_lists_open_applications(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Siti Rahman');
    }

    public function test_the_admin_overview_is_staff_only(): void
    {
        $this->actingAs($this->rescuer)
            ->get(route('admin.applications.index'))
            ->assertForbidden();
    }
}
