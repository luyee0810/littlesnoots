<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderOnboardingTest extends TestCase
{
    use RefreshDatabase;

    /** @param  array<string, mixed>  $overrides */
    private function profilePayload(array $overrides = []): array
    {
        return [
            'headline' => 'Cat-obsessed sitter with a quiet spare room',
            'bio' => 'Ten years of looking after cats in my own home.',
            'city' => 'Petaling Jaya',
            'state' => 'Selangor',
            'postcode' => '46000',
            'service_radius_km' => 10,
            'years_experience' => 10,
            'max_pets_per_booking' => 2,
            ...$overrides,
        ];
    }

    public function test_guests_cannot_reach_onboarding(): void
    {
        $this->get(route('provider.onboarding'))->assertRedirect(route('login'));
    }

    public function test_a_user_can_become_a_provider(): void
    {
        $user = User::factory()->create(['name' => 'Nurul Aisyah']);

        $this->actingAs($user)
            ->post(route('provider.onboarding.store'), $this->profilePayload())
            ->assertRedirect(route('provider.services.create'));

        $profile = $user->refresh()->providerProfile;

        $this->assertNotNull($profile);
        $this->assertSame('nurul-aisyah', $profile->slug);
        $this->assertSame('Petaling Jaya', $profile->city);

        // Sitters are moderated: signing up puts you in the queue, not on the site.
        $this->assertSame('pending', $profile->status);
        $this->assertFalse($profile->isLive());
        $this->assertFalse($user->isProvider());
    }

    public function test_becoming_a_provider_does_not_change_the_users_role(): void
    {
        $user = User::factory()->create(['role' => 'adopter']);

        $this->actingAs($user)->post(route('provider.onboarding.store'), $this->profilePayload());

        $this->assertSame('adopter', $user->refresh()->role);

        // isProvider() tracks an *approved* profile, so it stays false until a
        // moderator approves — the role column is untouched either way.
        $this->assertFalse($user->isProvider());

        $user->providerProfile->markApproved(User::factory()->create(['role' => 'staff']));

        $this->assertTrue($user->fresh()->isProvider());
        $this->assertSame('adopter', $user->fresh()->role);
    }

    public function test_slugs_do_not_collide(): void
    {
        $first = User::factory()->create(['name' => 'Wei Ming Tan']);
        $second = User::factory()->create(['name' => 'Wei Ming Tan']);

        $this->actingAs($first)->post(route('provider.onboarding.store'), $this->profilePayload());
        $this->actingAs($second)->post(route('provider.onboarding.store'), $this->profilePayload());

        $this->assertSame('wei-ming-tan', $first->refresh()->providerProfile->slug);
        $this->assertSame('wei-ming-tan-2', $second->refresh()->providerProfile->slug);
    }

    public function test_a_headline_and_city_are_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('provider.onboarding.store'), $this->profilePayload(['headline' => '', 'city' => '']))
            ->assertSessionHasErrors(['headline', 'city']);
    }

    public function test_an_existing_provider_is_sent_to_their_services(): void
    {
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($profile->user)
            ->get(route('provider.onboarding'))
            ->assertRedirect(route('provider.services.index'));
    }

    public function test_a_provider_can_add_a_service_and_the_unit_comes_from_the_category(): void
    {
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())->create();
        $category = ServiceCategory::factory()->create(['pricing_unit' => 'night']);

        $this->actingAs($profile->user)
            ->post(route('provider.services.store'), [
                'service_category_id' => $category->id,
                'price' => 60,
                'min_units' => 1,
                'max_pets' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('provider.services.index'));

        $service = $profile->services()->first();

        $this->assertEquals(60.00, (float) $service->price);
        $this->assertSame('night', $service->price_unit);
        $this->assertSame('MYR', $service->currency);
    }

    public function test_a_provider_cannot_list_the_same_category_twice(): void
    {
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())->create();
        $category = ServiceCategory::factory()->create();

        ProviderService::factory()->forCategory($category)
            ->create(['provider_profile_id' => $profile->id]);

        $this->actingAs($profile->user)
            ->post(route('provider.services.store'), [
                'service_category_id' => $category->id,
                'price' => 60,
                'min_units' => 1,
                'max_pets' => 2,
            ])
            ->assertSessionHasErrors('service_category_id');
    }

    public function test_a_provider_cannot_edit_someone_elses_service(): void
    {
        $mine = ProviderProfile::factory()->forUser(User::factory()->create())->create();
        $theirs = ProviderProfile::factory()->forUser(User::factory()->create())->create();

        $service = ProviderService::factory()->forCategory(ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $theirs->id]);

        $this->actingAs($mine->user)
            ->get(route('provider.services.edit', $service))
            ->assertForbidden();
    }

    public function test_a_service_with_bookings_is_hidden_rather_than_deleted(): void
    {
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())->create();
        $service = ProviderService::factory()->forCategory(ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $profile->id]);

        Booking::factory()->forService($service)->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($profile->user)
            ->delete(route('provider.services.destroy', $service))
            ->assertRedirect();

        $this->assertDatabaseHas('provider_services', ['id' => $service->id, 'is_active' => false]);
    }

    public function test_a_service_without_bookings_is_deleted(): void
    {
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())->create();
        $service = ProviderService::factory()->forCategory(ServiceCategory::factory()->create())
            ->create(['provider_profile_id' => $profile->id]);

        $this->actingAs($profile->user)
            ->delete(route('provider.services.destroy', $service));

        $this->assertDatabaseMissing('provider_services', ['id' => $service->id]);
    }

    public function test_a_provider_can_update_their_profile(): void
    {
        $profile = ProviderProfile::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($profile->user)
            ->put(route('provider.profile.update'), $this->profilePayload(['city' => 'Ipoh']))
            ->assertRedirect(route('provider.profile.edit'));

        $this->assertSame('Ipoh', $profile->refresh()->city);
    }
}
