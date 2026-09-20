<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Report;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Notifications\BookingMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $sitter;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->sitter = User::factory()->create();
        $this->owner = User::factory()->create();

        $provider = ProviderProfile::factory()->forUser($this->sitter)->create();

        $service = ProviderService::factory()
            ->forCategory(ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $provider->id]);

        $this->booking = Booking::factory()->forService($service)->create([
            'user_id' => $this->owner->id,
            'status' => 'accepted',
        ]);
    }

    public function test_both_sides_can_message_each_other(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'What time should I drop her off?'])
            ->assertRedirect();

        $this->actingAs($this->sitter)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'Any time after 9 works.']);

        $this->assertSame(2, $this->booking->messages()->count());
    }

    public function test_the_other_side_is_emailed(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'What time should I drop her off?']);

        Notification::assertSentTo($this->sitter, BookingMessageReceived::class);
        Notification::assertNotSentTo($this->owner, BookingMessageReceived::class);
    }

    public function test_outsiders_cannot_read_or_post(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('bookings.show', $this->booking))->assertForbidden();

        $this->actingAs($stranger)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'Butting in.'])
            ->assertForbidden();

        $this->assertSame(0, $this->booking->messages()->count());
    }

    public function test_an_admin_can_read_the_thread_but_not_post_in_it(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'Dropping her at nine.']);

        $admin = User::factory()->create(['role' => 'admin']);

        // Readable, for disputes.
        $this->actingAs($admin)
            ->get(route('bookings.show', $this->booking))
            ->assertOk()
            ->assertSee('Dropping her at nine.');

        // But an admin must not be able to speak as a party to the booking.
        $this->actingAs($admin)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'Admin here.'])
            ->assertForbidden();
    }

    public function test_unread_counts_track_the_other_side_only(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'First question.']);

        $this->assertSame(1, $this->booking->unreadCountFor($this->sitter));
        $this->assertSame(0, $this->booking->unreadCountFor($this->owner), 'Your own message is not unread for you.');
    }

    public function test_opening_the_booking_marks_the_thread_read(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'First question.']);

        $this->actingAs($this->sitter)->get(route('bookings.show', $this->booking))->assertOk();

        $this->assertSame(0, $this->booking->unreadCountFor($this->sitter));
    }

    public function test_an_empty_message_is_rejected(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.messages.store', $this->booking), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertSame(0, $this->booking->messages()->count());
    }

    public function test_a_message_can_be_reported(): void
    {
        $this->actingAs($this->sitter)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'Something inappropriate.']);

        $message = $this->booking->messages()->first();

        $this->actingAs($this->owner)->post(route('reports.store'), [
            'type' => 'booking-message',
            'id' => $message->id,
            'reason' => 'abusive',
        ]);

        $this->assertSame(1, Report::count());
        $this->assertTrue($message->fresh()->isReportedBy($this->owner));
    }

    public function test_the_dashboard_separates_upcoming_from_past_and_shows_unread(): void
    {
        $this->actingAs($this->sitter)
            ->post(route('bookings.messages.store', $this->booking), ['body' => 'See you then.']);

        $this->actingAs($this->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Upcoming bookings')
            ->assertSee('unread');
    }

    public function test_the_dashboard_nudges_for_a_review_after_a_completed_booking(): void
    {
        $this->booking->update(['status' => 'completed']);

        $this->actingAs($this->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Past bookings')
            ->assertSee('Leave a review');
    }
}
