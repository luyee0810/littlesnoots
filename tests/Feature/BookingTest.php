<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private ProviderProfile $provider;

    private ProviderService $service;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::factory()->create([
            'name' => 'Pet Boarding',
            'slug' => 'boarding',
            'pricing_unit' => 'night',
            'requires_date_range' => true,
        ]);

        $this->provider = ProviderProfile::factory()
            ->forUser(User::factory()->create())
            ->create(['available_days' => null]);

        $this->service = ProviderService::factory()->forCategory($category)->create([
            'provider_profile_id' => $this->provider->id,
            'price' => 50,
            'additional_pet_price' => 20,
            'max_pets' => 3,
            'min_units' => 1,
        ]);

        $this->owner = User::factory()->create();
    }

    /** @param  array<string, mixed>  $overrides */
    private function payload(array $overrides = []): array
    {
        return [
            'provider_service_id' => $this->service->id,
            'starts_at' => now()->addWeek()->setTime(9, 0)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addWeek()->addDays(3)->setTime(9, 0)->format('Y-m-d\TH:i'),
            'pet_name' => 'Mochi',
            'pet_count' => 1,
            'owner_name' => $this->owner->name,
            'owner_email' => $this->owner->email,
            'message' => 'She is shy but settles quickly.',
            ...$overrides,
        ];
    }

    public function test_guests_cannot_book(): void
    {
        $this->post(route('bookings.store', $this->provider), $this->payload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_an_owner_can_request_a_booking(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload())
            ->assertRedirect();

        $booking = Booking::first();

        $this->assertNotNull($booking);
        $this->assertSame('pending', $booking->status);
        $this->assertSame($this->provider->id, $booking->provider_profile_id);
        $this->assertSame($this->owner->id, $booking->user_id);
        $this->assertNotNull($booking->reference);
        $this->assertNotNull($booking->expires_at);
    }

    public function test_the_total_is_calculated_from_the_service_not_the_form(): void
    {
        // 3 nights at RM 50, one pet — and a forged total in the request.
        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload([
                'total' => 1,
                'unit_price' => 1,
            ]));

        $booking = Booking::first();

        $this->assertSame(3, $booking->unit_quantity);
        $this->assertEquals(50.00, (float) $booking->unit_price);
        $this->assertEquals(150.00, (float) $booking->total);
    }

    public function test_extra_pets_are_charged_per_night(): void
    {
        // 3 nights, 2 pets → 3 × (50 + 20)
        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload(['pet_count' => 2]));

        $this->assertEquals(210.00, (float) Booking::first()->total);
    }

    public function test_a_booking_cannot_exceed_the_max_pets_for_the_service(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload(['pet_count' => 9]))
            ->assertSessionHasErrors('pet_count');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_a_booking_must_start_in_the_future(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload([
                'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasErrors('starts_at');
    }

    public function test_a_date_range_service_requires_an_end_date(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload(['ends_at' => null]))
            ->assertSessionHasErrors('ends_at');
    }

    public function test_a_service_belonging_to_another_provider_is_rejected(): void
    {
        $other = ProviderProfile::factory()->forUser(User::factory()->create())->create();
        $otherService = ProviderService::factory()
            ->forCategory(ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $other->id]);

        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload([
                'provider_service_id' => $otherService->id,
            ]))
            ->assertSessionHasErrors('provider_service_id');
    }

    public function test_a_booking_on_a_blocked_date_is_rejected(): void
    {
        $date = now()->addWeek()->startOfDay();

        $this->provider->unavailableDates()->create(['date' => $date->toDateString(), 'reason' => 'Away']);

        $this->actingAs($this->owner)
            ->post(route('bookings.store', $this->provider), $this->payload([
                'starts_at' => $date->copy()->setTime(9, 0)->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasErrors('starts_at');
    }

    public function test_only_the_two_parties_can_view_a_booking(): void
    {
        $booking = Booking::factory()->forService($this->service, 2)
            ->create(['user_id' => $this->owner->id]);

        $this->actingAs($this->owner)->get(route('bookings.show', $booking))->assertOk();
        $this->actingAs($this->provider->user)->get(route('bookings.show', $booking))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('bookings.show', $booking))->assertForbidden();
    }
}
