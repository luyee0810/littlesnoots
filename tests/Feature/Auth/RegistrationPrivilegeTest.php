<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationPrivilegeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Registering as a "shelter" used to set role = staff, which now means the
     * whole /admin area. Nobody grants themselves that from a public form.
     */
    public function test_signing_up_as_a_shelter_does_not_grant_staff(): void
    {
        $this->post('/register', [
            'name' => 'Not A Shelter',
            'email' => 'sneaky@example.com',
            'password' => 'momo12345',
            'password_confirmation' => 'momo12345',
            'account_type' => 'shelter',
        ]);

        $user = User::firstWhere('email', 'sneaky@example.com');

        $this->assertSame('adopter', $user->role);
        $this->assertFalse($user->isStaff());

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_no_account_type_grants_staff(): void
    {
        foreach (['adopter', 'shelter', 'provider'] as $i => $type) {
            // Registering signs you in, and /register is guest-only.
            auth()->logout();

            $this->post('/register', [
                'name' => 'Member '.$i,
                'email' => "member{$i}@example.com",
                'password' => 'momo12345',
                'password_confirmation' => 'momo12345',
                'account_type' => $type,
            ]);

            $this->assertSame('adopter', User::firstWhere('email', "member{$i}@example.com")->role);
        }

        $this->assertSame(0, User::whereIn('role', ['staff', 'admin'])->count());
    }

    public function test_rehoming_needs_no_role_at_all(): void
    {
        // The point of the old mapping was listing pets; that's open to everyone.
        $member = User::factory()->create(['role' => 'adopter']);

        $this->actingAs($member)->get(route('listings.create'))->assertOk();
    }
}
