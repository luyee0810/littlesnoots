<?php

namespace Tests\Feature\Auth;

use App\Models\Species;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_sends_a_verification_link(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Nurul Aisyah',
            'email' => 'nurul@example.com',
            'password' => 'snoots123',
            'password_confirmation' => 'snoots123',
            'account_type' => 'adopter',
        ]);

        $user = User::firstWhere('email', 'nurul@example.com');

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_the_signed_link_verifies_the_address(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_a_tampered_link_does_not_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('someone-elses@example.com'),
        ]);

        $this->actingAs($user)->get($url)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    // ---- What verification gates ---------------------------------------

    public function test_an_unverified_member_cannot_list_a_pet(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('listings.create'))->assertRedirect(route('verification.notice'));

        $this->actingAs($user)->post(route('listings.store'), [
            'name' => 'Luna',
            'species_id' => Species::create(['name' => 'Cat', 'slug' => 'cat'])->id,
            'description' => 'Found under a car in Bangsar.',
            'age_group' => 'young',
            'gender' => 'female',
            'size' => 'small',
            'status' => 'available',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('pets', 0);
    }

    public function test_an_unverified_member_can_still_browse_and_use_their_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        // Verification gates writing that reaches other people, not the site itself.
        $this->actingAs($user)->get(route('pets.index'))->assertOk();
        $this->actingAs($user)->get(route('services.index'))->assertOk();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('memorials.index'))->assertOk();
    }

    public function test_a_verified_member_is_unaffected(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('listings.create'))
            ->assertOk();
    }

    public function test_the_notice_page_offers_another_link(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.notice'))->assertOk()->assertSee($user->email);

        $this->actingAs($user)->post(route('verification.send'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_an_already_verified_member_is_sent_onwards(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('verification.notice'))
            ->assertRedirect(route('dashboard'));
    }
}
