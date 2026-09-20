<?php

namespace Tests\Feature;

use App\Models\ProviderPhoto;
use App\Models\ProviderProfile;
use App\Models\User;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private ProviderProfile $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->provider = ProviderProfile::factory()->pendingApproval()->create();

        // With a photo: the admin views render one, and a factory-made profile
        // has none — which is how a broken photo call reached the browser.
        ProviderPhoto::create([
            'provider_profile_id' => $this->provider->id,
            'url' => '/images/seed/sitters/sitter-01.jpg',
            'caption' => 'The spare room',
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    public function test_a_new_sitter_lands_in_the_queue_rather_than_going_live(): void
    {
        $this->seed(ServiceCategorySeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('provider.onboarding.store'), [
            'headline' => 'Cat-obsessed sitter with a quiet spare room',
            'bio' => 'Ten years of fostering cats in Bangsar.',
            'years_experience' => 5,
            'city' => 'Petaling Jaya',
            'service_radius_km' => 10,
            'max_pets_per_booking' => 2,
        ]);

        $profile = $user->fresh()->providerProfile;

        $this->assertNotNull($profile);
        $this->assertSame('pending', $profile->status);
        $this->assertNull($profile->published_at);
        $this->assertFalse($profile->isLive());
    }

    public function test_a_pending_sitter_is_not_publicly_visible(): void
    {
        $this->get(route('providers.show', $this->provider))->assertForbidden();
        $this->get(route('services.index'))->assertDontSee($this->provider->headline);
    }

    public function test_only_staff_may_moderate_sitters(): void
    {
        $this->get(route('admin.providers.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.providers.index'))->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.providers.approve', $this->provider))->assertForbidden();
    }

    public function test_approving_lists_the_sitter(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('admin.providers.approve', $this->provider))
            ->assertRedirect(route('admin.providers.index'));

        $provider = $this->provider->fresh();

        $this->assertSame('approved', $provider->status);
        $this->assertNotNull($provider->published_at);
        $this->assertSame($this->staff->id, $provider->reviewed_by);
        $this->assertTrue($provider->isLive());

        auth()->logout();
        $this->get(route('providers.show', $provider))->assertOk();
    }

    public function test_the_owning_user_becomes_a_provider_only_once_approved(): void
    {
        $user = $this->provider->user;

        $this->assertFalse($user->isProvider(), 'A pending sitter is not yet a provider.');

        $this->provider->markApproved($this->staff);

        $this->assertTrue($user->fresh()->isProvider());
    }

    public function test_suspending_takes_a_sitter_down_with_a_reason(): void
    {
        $this->provider->markApproved($this->staff);

        $this->actingAs($this->staff)->patch(route('admin.providers.suspend', $this->provider), [
            'review_notes' => 'Please add a photo of the room pets would stay in.',
        ]);

        $provider = $this->provider->fresh();

        $this->assertSame('suspended', $provider->status);
        $this->assertNull($provider->published_at);
        $this->assertFalse($provider->isLive());

        auth()->logout();
        $this->get(route('providers.show', $provider))->assertForbidden();
    }

    public function test_suspension_requires_a_reason(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('admin.providers.suspend', $this->provider), ['review_notes' => 'no'])
            ->assertSessionHasErrors('review_notes');

        $this->assertSame('pending', $this->provider->fresh()->status);
    }

    public function test_a_suspended_sitter_sees_why_and_can_resubmit(): void
    {
        $this->provider->markSuspended($this->staff, 'Please add a photo of the room pets would stay in.');

        $this->actingAs($this->provider->user)
            ->get(route('provider.profile.edit'))
            ->assertOk()
            ->assertSee('Please add a photo of the room pets would stay in.');

        $this->actingAs($this->provider->user)->post(route('provider.resubmit'));

        $provider = $this->provider->fresh();

        $this->assertSame('pending', $provider->status);
        $this->assertNull($provider->review_notes);
    }

    public function test_the_admin_sitter_pages_render(): void
    {
        $this->actingAs($this->staff)
            ->get(route('admin.providers.index'))
            ->assertOk()
            ->assertSee($this->provider->user->name);

        $this->actingAs($this->staff)
            ->get(route('admin.providers.show', $this->provider))
            ->assertOk()
            ->assertSee($this->provider->headline);
    }
}
