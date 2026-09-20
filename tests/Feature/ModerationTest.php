<?php

namespace Tests\Feature;

use App\Models\MemorialMessage;
use App\Models\PetMemorial;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;      // whose memorial it is

    private User $author;     // who wrote the message

    private User $staff;

    private PetMemorial $memorial;

    private MemorialMessage $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->author = User::factory()->create();
        $this->staff = User::factory()->create(['role' => 'staff']);

        $this->memorial = PetMemorial::create([
            'user_id' => $this->owner->id,
            'slug' => 'biscuit',
            'pet_name' => 'Biscuit',
            'tribute' => 'The best girl.',
        ]);

        $this->message = $this->memorial->messages()->create([
            'user_id' => $this->author->id,
            'body' => 'Something unkind about a dead pet.',
        ]);
    }

    // ---- Deletion ------------------------------------------------------

    public function test_the_memorial_owner_can_delete_a_message(): void
    {
        $this->actingAs($this->owner)
            ->delete(route('memorials.messages.destroy', $this->message))
            ->assertRedirect();

        $this->assertModelMissing($this->message);
    }

    public function test_the_author_can_delete_their_own_message(): void
    {
        $this->actingAs($this->author)->delete(route('memorials.messages.destroy', $this->message));

        $this->assertModelMissing($this->message);
    }

    public function test_staff_can_delete_any_message(): void
    {
        $this->actingAs($this->staff)->delete(route('memorials.messages.destroy', $this->message));

        $this->assertModelMissing($this->message);
    }

    public function test_a_stranger_cannot_delete_a_message(): void
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('memorials.messages.destroy', $this->message))
            ->assertForbidden();

        $this->assertModelExists($this->message);
    }

    public function test_guests_cannot_delete_messages(): void
    {
        $this->delete(route('memorials.messages.destroy', $this->message))
            ->assertRedirect(route('login'));

        $this->assertModelExists($this->message);
    }

    // ---- Reporting -----------------------------------------------------

    public function test_a_reader_can_report_a_message(): void
    {
        $reporter = User::factory()->create();

        $this->actingAs($reporter)->post(route('reports.store'), [
            'type' => 'memorial-message',
            'id' => $this->message->id,
            'reason' => 'abusive',
            'notes' => 'This is cruel to the family.',
        ]);

        $report = Report::first();

        $this->assertSame('open', $report->status);
        $this->assertSame($reporter->id, $report->user_id);
        $this->assertTrue($this->message->fresh()->isReportedBy($reporter));

        // Reporting hides nothing on its own.
        $this->assertModelExists($this->message);
    }

    public function test_reporting_twice_updates_rather_than_erroring(): void
    {
        $reporter = User::factory()->create();

        foreach (['spam', 'abusive'] as $reason) {
            $this->actingAs($reporter)->post(route('reports.store'), [
                'type' => 'memorial-message',
                'id' => $this->message->id,
                'reason' => $reason,
            ]);
        }

        $this->assertSame(1, Report::count());
        $this->assertSame('abusive', Report::first()->reason);
    }

    public function test_only_known_content_types_can_be_reported(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reports.store'), [
                'type' => 'users',          // not reportable
                'id' => $this->owner->id,
                'reason' => 'abusive',
            ])
            ->assertSessionHasErrors('type');

        $this->assertSame(0, Report::count());
    }

    // ---- The admin queue -----------------------------------------------

    public function test_the_queue_is_staff_only(): void
    {
        $this->actingAs($this->owner)->get(route('admin.reports.index'))->assertForbidden();

        auth()->logout();
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    }

    public function test_staff_can_remove_reported_content(): void
    {
        $report = $this->report();

        $this->actingAs($this->staff)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Something unkind');

        $this->actingAs($this->staff)->patch(route('admin.reports.remove', $report));

        $this->assertModelMissing($this->message);
        $this->assertSame('actioned', $report->fresh()->status);
        $this->assertSame($this->staff->id, $report->fresh()->reviewed_by);
    }

    public function test_removing_closes_every_report_on_the_same_item(): void
    {
        $first = $this->report();
        $second = $this->report(User::factory()->create());

        $this->actingAs($this->staff)->patch(route('admin.reports.remove', $first));

        $this->assertSame('actioned', $second->fresh()->status);
    }

    public function test_dismissing_leaves_the_content_alone(): void
    {
        $report = $this->report();

        $this->actingAs($this->staff)->patch(route('admin.reports.dismiss', $report));

        $this->assertSame('dismissed', $report->fresh()->status);
        $this->assertModelExists($this->message);
    }

    public function test_deleting_content_closes_its_open_reports(): void
    {
        $report = $this->report();

        $this->actingAs($this->owner)->delete(route('memorials.messages.destroy', $this->message));

        $this->assertSame('actioned', $report->fresh()->status);
    }

    private function report(?User $reporter = null): Report
    {
        return Report::create([
            'reportable_type' => $this->message->getMorphClass(),
            'reportable_id' => $this->message->id,
            'user_id' => ($reporter ?? User::factory()->create())->id,
            'reason' => 'abusive',
            'status' => 'open',
        ]);
    }
}
