<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_users_can_register_as_adopters(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ada Adopter',
            'email' => 'ada@example.com',
            'account_type' => 'adopter',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'role' => 'adopter',
        ]);
    }

    public function test_new_users_can_register_as_shelters(): void
    {
        $this->post('/register', [
            'name' => 'Riley Rescue',
            'email' => 'riley@example.com',
            'account_type' => 'shelter',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'riley@example.com',
            'role' => 'staff',
        ]);
    }

    public function test_service_providers_are_sent_to_onboarding(): void
    {
        $response = $this->post('/register', [
            'name' => 'Sam Sitter',
            'email' => 'sam@example.com',
            'account_type' => 'provider',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('provider.onboarding'));

        // Providing services is a profile, not a role — the user stays an adopter.
        $this->assertDatabaseHas('users', [
            'email' => 'sam@example.com',
            'role' => 'adopter',
        ]);
    }

    public function test_registration_requires_an_account_type(): void
    {
        $this->post('/register', [
            'name' => 'No Type',
            'email' => 'notype@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('account_type');

        $this->assertGuest();
    }

    public function test_account_type_cannot_escalate_to_admin(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'account_type' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('account_type');

        $this->assertGuest();
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ada Adopter',
            'email' => 'ada@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}
