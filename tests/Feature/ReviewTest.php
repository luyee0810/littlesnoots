<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Review;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
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

    private function completedBooking(): Booking
    {
        return Booking::factory()->forService($this->service)->completed()
            ->create(['user_id' => $this->owner->id]);
    }

    public function test_guests_cannot_review(): void
    {
        $this->post(route('bookings.review.store', $this->completedBooking()), ['rating' => 5])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_an_owner_can_review_a_completed_booking(): void
    {
        $booking = $this->completedBooking();

        $this->actingAs($this->owner)
            ->post(route('bookings.review.store', $booking), ['rating' => 5, 'body' => 'Mochi loved her stay.'])
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'provider_profile_id' => $this->provider->id,
            'user_id' => $this->owner->id,
            'rating' => 5,
        ]);

        $this->provider->refresh();
        $this->assertSame(1, $this->provider->reviews_count);
        $this->assertEquals(5.0, (float) $this->provider->rating_avg);
    }

    public function test_the_rating_is_averaged_across_reviews(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.review.store', $this->completedBooking()), ['rating' => 5]);
        $this->actingAs($this->owner)
            ->post(route('bookings.review.store', $this->completedBooking()), ['rating' => 4]);

        $this->provider->refresh();
        $this->assertSame(2, $this->provider->reviews_count);
        $this->assertEquals(4.5, (float) $this->provider->rating_avg);
    }

    public function test_a_booking_that_is_not_completed_cannot_be_reviewed(): void
    {
        $booking = Booking::factory()->forService($this->service)->accepted()
            ->create(['user_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->post(route('bookings.review.store', $booking), ['rating' => 5])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_a_booking_can_only_be_reviewed_once(): void
    {
        $booking = $this->completedBooking();

        $this->actingAs($this->owner)->post(route('bookings.review.store', $booking), ['rating' => 5]);
        $this->actingAs($this->owner)
            ->post(route('bookings.review.store', $booking), ['rating' => 1])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_only_the_owner_can_review(): void
    {
        $booking = $this->completedBooking();

        $this->actingAs($this->provider->user)
            ->post(route('bookings.review.store', $booking), ['rating' => 5])
            ->assertForbidden();
        $this->actingAs(User::factory()->create())
            ->post(route('bookings.review.store', $booking), ['rating' => 5])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_the_rating_must_be_between_one_and_five(): void
    {
        $this->actingAs($this->owner)
            ->post(route('bookings.review.store', $this->completedBooking()), ['rating' => 6])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_reviews_are_listed_on_the_sitter_profile(): void
    {
        $review = Review::factory()->forBooking($this->completedBooking())
            ->create(['rating' => 4, 'body' => 'Sent photo updates every evening.']);
        $this->provider->refreshRating();

        $this->get(route('providers.show', $this->provider))
            ->assertOk()
            ->assertSee('href="#reviews"', false)
            ->assertSee('Sent photo updates every evening.')
            ->assertSee($review->reviewerName());
    }

    public function test_the_owner_sees_the_review_form_on_a_completed_booking(): void
    {
        $booking = $this->completedBooking();

        $this->actingAs($this->owner)
            ->get(route('bookings.show', $booking))
            ->assertSee(route('bookings.review.store', $booking));

        Review::factory()->forBooking($booking)->create(['body' => 'Lovely sitter.']);

        $this->actingAs($this->owner)
            ->get(route('bookings.show', $booking))
            ->assertDontSee(route('bookings.review.store', $booking))
            ->assertSee('Lovely sitter.');
    }
}
