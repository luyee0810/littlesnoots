<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->member = User::factory()->create(['role' => 'adopter']);
    }

    public function test_members_are_staff_only(): void
    {
        $this->actingAs($this->member)->get(route('admin.users.index'))->assertForbidden();

        auth()->logout();
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_staff_can_search_members(): void
    {
        $this->actingAs($this->staff)
            ->get(route('admin.users.index', ['q' => $this->member->email]))
            ->assertOk()
            ->assertSee($this->member->name)
            ->assertDontSee($this->admin->email);
    }

    public function test_a_member_page_shows_what_they_have_done(): void
    {
        $this->actingAs($this->staff)
            ->get(route('admin.users.show', $this->member))
            ->assertOk()
            ->assertSee($this->member->email)
            ->assertSee('Bookings made');
    }

    // ---- Suspension ----------------------------------------------------

    public function test_staff_can_suspend_a_member(): void
    {
        $this->actingAs($this->staff)->patch(route('admin.users.suspend', $this->member), [
            'suspension_reason' => 'Repeated abusive messages on memorials.',
        ]);

        $member = $this->member->fresh();

        $this->assertTrue($member->isSuspended());
        $this->assertSame($this->staff->id, $member->suspended_by);
    }

    public function test_suspension_requires_a_reason(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('admin.users.suspend', $this->member), ['suspension_reason' => 'no'])
            ->assertSessionHasErrors('suspension_reason');

        $this->assertFalse($this->member->fresh()->isSuspended());
    }

    public function test_a_suspended_member_is_turned_out_of_the_site(): void
    {
        $this->member->suspend($this->staff, 'Repeated abusive messages on memorials.');

        // Mid-session: the next request ends it, wherever they are.
        $this->actingAs($this->member)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_a_suspended_member_cannot_log_in(): void
    {
        $member = User::factory()->create(['password' => bcrypt('snoots123')]);
        $member->suspend($this->staff, 'Repeated abusive messages on memorials.');

        $this->post('/login', ['email' => $member->email, 'password' => 'snoots123']);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_lifting_a_suspension_restores_access(): void
    {
        $this->member->suspend($this->staff, 'Repeated abusive messages on memorials.');

        $this->actingAs($this->staff)->patch(route('admin.users.reinstate', $this->member));

        $this->assertFalse($this->member->fresh()->isSuspended());

        $this->actingAs($this->member->fresh())->get(route('dashboard'))->assertOk();
    }

    public function test_nobody_can_suspend_themselves(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.suspend', $this->admin), [
                'suspension_reason' => 'Locking myself out by accident.',
            ])
            ->assertForbidden();

        $this->assertFalse($this->admin->fresh()->isSuspended());
    }

    public function test_staff_cannot_suspend_each_other(): void
    {
        $other = User::factory()->create(['role' => 'staff']);

        $this->actingAs($this->staff)
            ->patch(route('admin.users.suspend', $other), [
                'suspension_reason' => 'A disagreement between colleagues.',
            ])
            ->assertForbidden();

        $this->assertFalse($other->fresh()->isSuspended());
    }

    public function test_an_admin_can_suspend_staff(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.users.suspend', $this->staff), [
            'suspension_reason' => 'Account compromised — locking it while we check.',
        ]);

        $this->assertTrue($this->staff->fresh()->isSuspended());
    }

    // ---- Roles ---------------------------------------------------------

    public function test_an_admin_can_promote_a_member_to_staff(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.role', $this->member), ['role' => 'staff']);

        $this->assertSame('staff', $this->member->fresh()->role);
        $this->actingAs($this->member->fresh())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_staff_cannot_change_roles(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('admin.users.role', $this->member), ['role' => 'admin'])
            ->assertForbidden();

        $this->assertSame('adopter', $this->member->fresh()->role);
    }

    public function test_an_admin_cannot_demote_themselves(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.role', $this->admin), ['role' => 'adopter'])
            ->assertForbidden();

        $this->assertSame('admin', $this->admin->fresh()->role);
    }
}
