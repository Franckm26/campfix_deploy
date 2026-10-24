<?php

namespace Tests\Feature;

use App\Http\Controllers\EventRequestController;
use App\Models\EventRequest;
use App\Models\User;
use App\Services\FacilityApprovalProgress;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FacilityApprovalProgressTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropAllTables();
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid'); $table->string('name'); $table->string('email');
            $table->string('role'); $table->string('password')->nullable();
            $table->boolean('is_deleted')->default(false); $table->boolean('is_archived')->default(false); $table->timestamps();
        });
        Schema::create('event_requests', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->string('status')->default('Pending');
            $table->string('request_type')->nullable(); $table->string('education_level')->nullable();
            $table->json('approval_route')->nullable(); $table->json('approval_history')->nullable();
            $table->unsignedInteger('approval_level')->default(1);
            foreach (['', '_level_1', '_level_2', '_level_3'] as $suffix) {
                $table->unsignedBigInteger('approved_by'.$suffix)->nullable(); $table->timestamp('approved_at'.$suffix)->nullable();
            }
            $table->boolean('is_deleted')->default(false); $table->timestamps();
            $table->date('event_date')->nullable();
            foreach (['student', 'faculty', 'building_admin', 'school_admin', 'academic_head', 'program_head', 'mis', 'maintenance'] as $role) {
                $table->boolean($role.'_archived')->default(false);
            }
        });
    }

    public function test_pending_approval_page_loads_existing_requests_without_changing_approval_history(): void
    {
        $approver = User::forceCreate(['name' => 'School Approver', 'email' => 'approver@example.com', 'role' => 'school_admin']);
        $event = $this->event(['user_id' => $approver->id, 'event_date' => now()->addDays(2)->toDateString()]);
        $event->save();
        $before = $event->fresh()->getAttributes();
        $this->actingAs($approver);

        $view = app(EventRequestController::class)->adminIndex(Request::create('/events/admin'));

        $this->assertSame('admin.events', $view->name());
        $this->assertSame('pending', $view->getData()['viewType']);
        $this->assertSame([$event->id], $view->getData()['requests']->pluck('id')->all());
        $this->assertSame($before, $event->fresh()->getAttributes());
        $this->assertStringContainsString('3 / 4', $this->renderTracker($view->getData()['requests']->first()));
    }

    private function event(array $overrides = []): EventRequest
    {
        return new EventRequest(array_merge(['status' => 'Pending', 'approval_level' => 4,
            'request_type' => 'Non-Academic',
            'approval_route' => ['program_head', 'academic_head', 'building_admin', 'school_admin'],
            'approval_history' => [
                ['level' => 1, 'approver_id' => 11, 'approver' => 'Program approver', 'at' => '2026-10-22 10:00:00'],
                ['level' => 2, 'approver_id' => 12, 'approver' => 'Academic approver', 'at' => '2026-10-22 10:10:00'],
                ['level' => 3, 'approver_id' => 13, 'approver' => 'Building approver', 'at' => '2026-10-22 10:20:00'],
            ]], $overrides));
    }

    private function renderTracker(EventRequest $event): string
    {
        return Blade::render('<x-request-progress-tracker :request="$event" title="Approval Progress" />', compact('event'));
    }

    private function renderPdf(EventRequest $event): string
    {
        $event->save();
        $html = '';
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $pdf->shouldReceive('download')->once()->with('event-request-'.$event->id.'.pdf')->andReturn(response('PDF'));
        \PDF::shouldReceive('loadView')->once()->withArgs(function ($view, $data) use (&$html) {
            $this->assertSame('events.pdf', $view);
            $html = view($view, $data)->render();
            return true;
        })->andReturn($pdf);
        $before = $event->fresh()->getAttributes();
        app(EventRequestController::class)->generatePdf($event->id);
        $this->assertSame($before, $event->fresh()->getAttributes());
        return substr($html, strpos($html, '<!-- Approval History Table -->'));
    }

    public function test_pdf_uses_philippine_time_and_does_not_duplicate_final_approval(): void
    {
        $user = User::forceCreate(['name' => 'Final Approver', 'email' => 'final@example.com', 'role' => 'school_admin']);
        $event = $this->event(['user_id' => $user->id, 'status' => 'Approved',
            'approved_by' => $user->id, 'approved_at' => '2026-10-24 00:43:00',
            'approval_history' => [
                ['level' => 1, 'role' => 'Program Head', 'approver_id' => $user->id, 'approver' => $user->name, 'at' => '2026-10-24 00:41:00'],
                ['level' => 2, 'role' => 'Academic Head', 'approver_id' => $user->id, 'approver' => $user->name, 'at' => '2026-10-24T08:42:00+08:00'],
                ['level' => 4, 'role' => 'School Admin', 'approver_id' => $user->id, 'approver' => $user->name, 'at' => '2026-10-24 00:43:00'],
            ]]);
        $event->created_at = '2026-10-23 16:37:00';
        $html = $this->renderPdf($event);
        $this->assertStringContainsString('Date/Time (PHT, UTC+08:00)', $html);
        $this->assertStringContainsString('10/24/2026 12:37 AM', $html);
        $this->assertStringContainsString('10/24/2026 08:41 AM', $html);
        $this->assertStringContainsString('10/24/2026 08:42 AM', $html);
        $this->assertSame(1, substr_count($html, '10/24/2026 08:43 AM'));
        $this->assertSame(1, substr_count($html, '<td>School Admin</td>'));
    }

    public function test_pdf_converts_legacy_approval_columns_and_keeps_final_approver_fallback(): void
    {
        $users = collect(['Program', 'Academic', 'Building', 'School'])->map(fn ($name) => User::forceCreate([
            'name' => $name, 'email' => strtolower($name).'@example.com', 'role' => 'school_admin',
        ]));
        $event = $this->event(['user_id' => $users[0]->id, 'status' => 'Approved', 'approval_route' => null,
            'approval_history' => [], 'approved_by' => $users[3]->id, 'approved_at' => '2026-10-24 16:43:00',
            'approved_by_level_1' => $users[0]->id, 'approved_at_level_1' => '2026-10-24 16:41:00',
            'approved_by_level_2' => $users[1]->id, 'approved_at_level_2' => '2026-10-24 16:42:00',
            'approved_by_level_3' => $users[2]->id, 'approved_at_level_3' => '2026-10-24 16:42:30']);
        $html = $this->renderPdf($event);
        $this->assertStringContainsString('10/25/2026 12:41 AM', $html);
        $this->assertSame(2, substr_count($html, '10/25/2026 12:42 AM'));
        $this->assertStringContainsString('10/25/2026 12:43 AM', $html);
        $this->assertSame(1, substr_count($html, '<td>School Admin</td>'));
    }

    public function test_saved_four_step_route_is_used_even_for_non_academic_requests_without_approver_accounts(): void
    {
        $event = $this->event();
        $progress = app(FacilityApprovalProgress::class)->forRequest($event);
        $this->assertSame(4, $progress['total']); $this->assertSame(3, $progress['completed']); $this->assertSame(75, $progress['percentage']);
        $this->assertSame(['program_head', 'academic_head', 'building_admin', 'school_admin'], array_column($progress['steps'], 'role'));
        $html = $this->renderTracker($event);
        $this->assertStringContainsString('3 / 4', $html); $this->assertStringContainsString('75%', $html);
        $this->assertStringContainsString('Program approver', $html);
    }

    public function test_custom_two_step_route_uses_sequential_history_and_ignores_legacy_level_columns(): void
    {
        $event = $this->event(['approval_route' => ['building_admin', 'school_admin'], 'approval_level' => 2,
            'approval_history' => [['level' => 1, 'approver_id' => 11, 'approver' => 'Building approver']],
            'approved_by_level_2' => 99]);
        $progress = app(FacilityApprovalProgress::class)->forRequest($event);
        $this->assertSame(2, $progress['total']); $this->assertSame(1, $progress['completed']); $this->assertSame(50, $progress['percentage']);
        $this->assertSame('pending', $progress['steps'][1]['state']);
        $this->assertStringNotContainsString('Program Head', $this->renderTracker($event));
    }

    public function test_reordered_configured_roles_keep_the_saved_order(): void
    {
        $event = $this->event(['approval_route' => ['academic_head', 'principal_assistant', 'school_admin'], 'approval_history' => [], 'approval_level' => 1]);
        $progress = app(FacilityApprovalProgress::class)->forRequest($event);
        $this->assertSame(['academic_head', 'principal_assistant', 'school_admin'], array_column($progress['steps'], 'role'));
        $this->assertSame(0, $progress['completed']);
    }

    public function test_legacy_non_academic_and_shs_routes_have_the_correct_steps(): void
    {
        $event = $this->event(['approval_route' => null, 'approval_history' => [], 'approval_level' => 4,
            'approved_by_level_3' => 11, 'approved_at_level_3' => '2026-10-22 10:00:00']);
        $progress = app(FacilityApprovalProgress::class)->forRequest($event);
        $this->assertSame(2, $progress['total']); $this->assertSame(1, $progress['completed']);
        $event->education_level = 'shs';
        $this->assertSame(['principal_assistant', 'academic_head', 'school_admin'], array_column(app(FacilityApprovalProgress::class)->forRequest($event)['steps'], 'role'));
    }

    public function test_rejected_and_cancelled_requests_do_not_mark_later_steps_as_approved_or_pending(): void
    {
        $event = $this->event(['status' => 'Rejected', 'approval_level' => 2,
            'approval_history' => [['level' => 1, 'approver_id' => 11, 'approver' => 'First'], ['level' => 2, 'approver_id' => 12, 'approver' => 'Second', 'action' => 'rejected']]]);
        $progress = app(FacilityApprovalProgress::class)->forRequest($event);
        $this->assertSame(['approved', 'rejected', 'waiting', 'waiting'], array_column($progress['steps'], 'state'));
        $event->status = 'Cancelled';
        $this->assertNotContains('pending', array_column(app(FacilityApprovalProgress::class)->forRequest($event)['steps'], 'state'));
    }

    public function test_approved_empty_route_does_not_invent_four_approvers(): void
    {
        $progress = app(FacilityApprovalProgress::class)->forRequest($this->event(['status' => 'Approved', 'approval_route' => [], 'approval_history' => []]));
        $this->assertSame(0, $progress['total']); $this->assertSame(100, $progress['percentage']);
    }

    public function test_fully_approved_configured_route_shows_all_steps_complete(): void
    {
        $event = $this->event(['status' => 'Approved', 'approval_route' => ['building_admin', 'school_admin']]);
        $progress = app(FacilityApprovalProgress::class)->forRequest($event);
        $this->assertSame(2, $progress['completed']); $this->assertSame(100, $progress['percentage']);
        $this->assertSame(['approved', 'approved'], array_column($progress['steps'], 'state'));
    }

    public function test_tracker_escapes_saved_approver_names(): void
    {
        $event = $this->event(['approval_history' => [['level' => 1, 'approver_id' => 11, 'approver' => '<script>alert(1)</script>']]]);
        $html = $this->renderTracker($event);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_request_modal_uses_the_same_tracker_as_pending_approval_without_changing_approvals(): void
    {
        $owner = User::forceCreate(['name' => 'Building Requester', 'email' => 'requester@example.com', 'role' => 'building_admin']);
        $event = $this->event(['user_id' => $owner->id]); $event->save();
        $before = $event->fresh()->getAttributes();
        $this->actingAs($owner);
        $response = app(EventRequestController::class)->show($event->id)->getData(true);
        $this->assertSame($this->renderTracker($event->fresh()), $response['event']['approval_progress_html']);
        $this->assertSame($before, $event->fresh()->getAttributes());
    }
}
