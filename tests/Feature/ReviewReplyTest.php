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

class ReviewReplyTest extends TestCase
{
    use RefreshDatabase;

    private ProviderProfile $provider;

    private Review $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = ProviderProfile::factory()
            ->forUser(User::factory()->create())
            ->create();

        $service = ProviderService::factory()
            ->forCategory(ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $this->provider->id]);

        // A review is always backed by a completed booking.
        $booking = Booking::factory()->forService($service)->completed()
            ->create(['user_id' => User::factory()->create()->id]);

        $this->review = Review::factory()->forBooking($booking)->create([
            'rating' => 2,
            'body' => 'The dog came back muddy and the pick-up was late.',
        ]);
    }

    public function test_the_sitter_can_reply_once(): void
    {
        $this->actingAs($this->provider->user)
            ->post(route('reviews.reply', $this->review), [
                'provider_reply' => 'Apologies — the rain was heavy that day, and I should have called ahead.',
            ])
            ->assertRedirect();

        $review = $this->review->fresh();

        $this->assertStringContainsString('the rain was heavy', $review->provider_reply);
        $this->assertNotNull($review->replied_at);
    }

    public function test_a_second_reply_is_refused(): void
    {
        $this->review->reply('First and only word.');

        $this->actingAs($this->provider->user)
            ->post(route('reviews.reply', $this->review), ['provider_reply' => 'Actually, let me rephrase that.'])
            ->assertForbidden();

        // The reply a reader already saw can't be quietly rewritten.
        $this->assertSame('First and only word.', $this->review->fresh()->provider_reply);
    }

    public function test_nobody_else_can_reply(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reviews.reply', $this->review), ['provider_reply' => 'Butting in here.'])
            ->assertForbidden();

        $this->actingAs($this->review->user)
            ->post(route('reviews.reply', $this->review), ['provider_reply' => 'Replying to myself.'])
            ->assertForbidden();

        $this->assertNull($this->review->fresh()->provider_reply);
    }

    public function test_the_reply_is_shown_on_the_public_profile(): void
    {
        $this->review->reply('Sorry about that — refunded the difference.');

        $this->get(route('providers.show', $this->provider))
            ->assertOk()
            ->assertSee('Sorry about that')
            ->assertSee('replied');
    }

    public function test_an_empty_reply_is_rejected(): void
    {
        $this->actingAs($this->provider->user)
            ->post(route('reviews.reply', $this->review), ['provider_reply' => ''])
            ->assertSessionHasErrors('provider_reply');

        $this->assertNull($this->review->fresh()->provider_reply);
    }

    public function test_a_review_can_be_reported(): void
    {
        $reporter = User::factory()->create();

        $this->actingAs($reporter)->post(route('reports.store'), [
            'type' => 'review',
            'id' => $this->review->id,
            'reason' => 'false',
            'notes' => 'This booking never happened.',
        ]);

        $this->assertTrue($this->review->fresh()->isReportedBy($reporter));
    }
}
