<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_adopters_see_the_applications_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'adopter']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('applications you');
    }

    public function test_pet_listers_see_the_listings_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('listed for adoption');
    }
}
