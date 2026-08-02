<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProviderBookingTest extends TestCase
{
    use RefreshDatabase;

    private ProviderProfile $provider;

    private ProviderService $service;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = ProviderProfile::factory()
            ->forUser(User::factory()->create())
            ->create();

        $this->service = ProviderService::factory()
            ->forCategory(ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $this->provider->id]);

        $this->owner = User::factory()->create();
    }

    private function booking(string $status = 'pending'): Booking
    {
        return Booking::factory()->forService($this->service, 2)->create([
            'user_id' => $this->owner->id,
            'status' => $status,
        ]);
    }

    public function test_the_inbox_needs_a_provider_profile(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('provider.bookings.index'))
            ->assertRedirect(route('provider.onboarding'));
    }

    public function test_the_inbox_lists_pending_requests_first(): void
    {
        $pending = $this->booking();
        $completed = $this->booking('completed');

        $this->actingAs($this->provider->user)
            ->get(route('provider.bookings.index'))
            ->assertOk()
            ->assertSee('Needs your response (1)')
            ->assertSee($pending->reference)
            ->assertSee($completed->pet_name);
    }

    public function test_a_provider_can_accept_a_pending_booking(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->provider->user)
            ->patch(route('provider.bookings.accept', $booking), ['provider_response' => 'See you then!'])
            ->assertRedirect();

        $booking->refresh();

        $this->assertSame('accepted', $booking->status);
        $this->assertSame('See you then!', $booking->provider_response);
        $this->assertNotNull($booking->responded_at);
    }

    public function test_a_provider_can_decline_a_pending_booking(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->provider->user)
            ->patch(route('provider.bookings.decline', $booking))
            ->assertRedirect();

        $this->assertSame('declined', $booking->refresh()->status);
    }

    public function test_the_owner_cannot_accept_their_own_booking(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->owner)
            ->patch(route('provider.bookings.accept', $booking))
            ->assertRedirect(route('provider.onboarding'));

        $this->assertSame('pending', $booking->refresh()->status);
    }

    public function test_an_unrelated_provider_cannot_respond(): void
    {
        $booking = $this->booking();

        $intruder = ProviderProfile::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($intruder->user)
            ->patch(route('provider.bookings.accept', $booking))
            ->assertForbidden();

        $this->assertSame('pending', $booking->refresh()->status);
    }

    public function test_an_already_answered_booking_cannot_be_answered_again(): void
    {
        $booking = $this->booking('accepted');

        $this->actingAs($this->provider->user)
            ->patch(route('provider.bookings.decline', $booking))
            ->assertForbidden();

        $this->assertSame('accepted', $booking->refresh()->status);
    }

    public function test_a_provider_can_complete_a_confirmed_booking(): void
    {
        $booking = $this->booking('accepted');

        $this->actingAs($this->provider->user)
            ->patch(route('provider.bookings.complete', $booking))
            ->assertRedirect();

        $booking->refresh();

        $this->assertSame('completed', $booking->status);
        $this->assertNotNull($booking->completed_at);
    }

    public function test_a_pending_booking_cannot_jump_straight_to_completed(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->provider->user)
            ->patch(route('provider.bookings.complete', $booking))
            ->assertForbidden();
    }

    public function test_either_party_can_cancel_a_live_booking(): void
    {
        $ownerSide = $this->booking('accepted');
        $providerSide = $this->booking('accepted');

        $this->actingAs($this->owner)
            ->patch(route('bookings.cancel', $ownerSide))
            ->assertRedirect();
        $this->assertSame('cancelled_by_owner', $ownerSide->refresh()->status);

        $this->actingAs($this->provider->user)
            ->patch(route('bookings.cancel', $providerSide))
            ->assertRedirect();
        $this->assertSame('cancelled_by_provider', $providerSide->refresh()->status);
    }

    public function test_a_completed_booking_cannot_be_cancelled(): void
    {
        $booking = $this->booking('completed');

        $this->actingAs($this->owner)
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_the_model_refuses_an_illegal_transition(): void
    {
        $booking = $this->booking('declined');

        $this->expectException(RuntimeException::class);

        $booking->markAccepted();
    }
}
