<?php

namespace Tests\Feature;

use App\Http\Controllers\RoleAnalyticsController;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Concern;
use App\Models\Report;
use App\Models\User;
use App\Notifications\TicketReopenedNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReopenTicketTest extends TestCase
{
    private User $reporter;

    private User $mis;

    private Concern $concern;

    private Report $report;

    protected function setUp(): void
    {
        parent::setUp();
        // Match other workflow tests: isolate this feature from legacy migration ordering.
        foreach (['users', 'categories', 'concerns', 'reports', 'activity_logs', 'concern_reporters', 'user_archived_reports', 'user_archived_concerns', 'maintenance_staff'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('maintenance_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        foreach (['concerns', 'reports'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('category_id');
                if ($name === 'reports') {
                    $table->unsignedBigInteger('concern_id')->nullable();
                }
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->string('priority')->nullable();
                $table->string('status');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('in_progress_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->decimal('cost')->nullable();
                $table->string('damaged_part')->nullable();
                $table->string('replaced_part')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamp('deleted_at')->nullable();
                $table->boolean('is_archived')->default(false);
                $table->boolean('mis_archived')->default(false);
                $table->timestamp('archived_at')->nullable();
                $table->timestamp('auto_delete_at')->nullable();
                $table->boolean('follow_up_sent')->default(false);
                $table->timestamp('follow_up_sent_at')->nullable();
                $table->timestamps();
            });
            Schema::create('user_archived_'.$name, function (Blueprint $table) use ($name) {
                $table->unsignedBigInteger($name === 'reports' ? 'report_id' : 'concern_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
            });
        }
        Schema::create('concern_reporters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concern_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_original')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('concern_id')->nullable();
            $table->unsignedBigInteger('report_id')->nullable();
            $table->string('action');
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Notification::fake();
        $this->reporter = $this->user('student');
        $this->mis = $this->user('mis');
        $category = Category::create(['name' => 'Technology/Internet']);
        $this->concern = Concern::create([
            'user_id' => $this->reporter->id, 'category_id' => $category->id,
            'title' => 'Internet unavailable', 'location' => 'Room 301', 'status' => 'Resolved',
            'assigned_to' => $this->mis->id, 'assigned_at' => now()->subDays(2),
            'resolved_at' => now()->subDay(), 'resolution_notes' => 'Replaced cable', 'cost' => 150,
        ]);
        $this->report = Report::create(array_merge($this->concern->only([
            'user_id', 'category_id', 'title', 'location', 'status', 'assigned_to', 'assigned_at', 'resolved_at', 'resolution_notes', 'cost',
        ]), ['concern_id' => $this->concern->id]));
        foreach ([$this->concern, $this->report] as $ticket) {
            $ticket->in_progress_at = now()->subDays(2);
            $ticket->save();
        }
    }

    public function test_original_reporter_reopens_both_records_and_preserves_resolution_history(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'The same connection is still failing.'])
            ->assertOk()->assertJsonPath('status', 'Assigned');
        foreach ([$this->concern->fresh(), $this->report->fresh()] as $ticket) {
            $this->assertSame('Assigned', $ticket->status);
            $this->assertNull($ticket->resolved_at);
            $this->assertNull($ticket->resolution_notes);
            $this->assertNull($ticket->in_progress_at);
            $this->assertTrue($ticket->assigned_at->isToday());
            $this->assertSame($this->mis->id, $ticket->assigned_to);
            $this->assertEquals(150, $ticket->cost);
        }
        $log = ActivityLog::where('action', 'concern_reopened')->firstOrFail();
        $this->assertSame($this->reporter->id, $log->user_id);
        $this->assertSame('Replaced cable', $log->old_values['resolution_notes']);
        $this->assertNotNull($log->old_values['resolved_at']);
        $this->assertSame('The same connection is still failing.', $log->metadata['reason']);
        $this->assertDatabaseCount('concerns', 1);
        $this->assertDatabaseCount('reports', 1);
        Notification::assertSentTo([$this->mis, $this->reporter], TicketReopenedNotification::class);
    }

    public function test_report_endpoint_synchronizes_linked_concern(): void
    {
        $this->actingAs($this->reporter)->postJson(route('reports.reopen', $this->report), ['reason' => 'Still broken'])->assertOk();
        $this->assertSame('Assigned', $this->concern->fresh()->status);
    }

    public function test_unassigned_ticket_returns_to_pending(): void
    {
        $this->concern->update(['assigned_to' => null]);
        $this->report->update(['assigned_to' => null]);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])
            ->assertOk()->assertJsonPath('status', 'Pending');
        $this->assertSame('Pending', $this->report->fresh()->status);
    }

    public function test_other_reporters_and_assigned_staff_cannot_reopen(): void
    {
        $other = $this->user('student');
        $this->concern->linkedUsers()->attach($other->id);
        foreach ([$other, $this->mis] as $user) {
            foreach (['concerns' => $this->concern, 'reports' => $this->report] as $type => $ticket) {
                $this->actingAs($user)->postJson(route($type.'.reopen', $ticket), ['reason' => 'Still broken'])->assertForbidden();
            }
        }
        $this->assertSame('Resolved', $this->concern->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_reason_is_required_and_limited(): void
    {
        foreach (['', '   ', str_repeat('x', 1001)] as $reason) {
            $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => $reason])
                ->assertUnprocessable()->assertJsonValidationErrors('reason');
        }
        $this->assertSame('Resolved', $this->report->fresh()->status);
    }

    public function test_active_and_deleted_tickets_cannot_reopen(): void
    {
        $this->concern->update(['status' => 'In Progress']);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertUnprocessable();
        $this->concern->update(['status' => 'Resolved', 'is_deleted' => true]);
        $this->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertUnprocessable();
        $this->assertSame('Resolved', $this->report->fresh()->status);
    }

    public function test_duplicate_reopen_does_not_create_another_history_entry(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        $this->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertUnprocessable();
        $this->assertDatabaseCount('activity_logs', 2);
    }

    public function test_archives_are_cleared_so_ticket_returns_to_work_queues(): void
    {
        foreach ([$this->concern, $this->report] as $ticket) {
            $ticket->update(['is_archived' => true, 'mis_archived' => true, 'archived_at' => now(), 'auto_delete_at' => now()->addDay()]);
            $ticket->archivedByUsers()->attach($this->mis->id);
        }
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        foreach ([$this->concern->fresh(), $this->report->fresh()] as $ticket) {
            $this->assertFalse($ticket->is_archived);
            $this->assertFalse($ticket->mis_archived);
            $this->assertNull($ticket->auto_delete_at);
            $this->assertSame(0, $ticket->archivedByUsers()->count());
        }
    }

    public function test_standalone_report_can_be_reopened_by_its_owner(): void
    {
        $this->report->update(['concern_id' => null]);
        $this->actingAs($this->reporter)->postJson(route('reports.reopen', $this->report), ['reason' => 'Still broken'])->assertOk();
        $this->assertSame('Assigned', $this->report->fresh()->status);
        $this->assertSame('Resolved', $this->concern->fresh()->status);
    }

    public function test_rooms_auto_resolution_does_not_close_reopened_tickets(): void
    {
        Category::whereKey($this->concern->category_id)->update(['name' => 'Rooms']);
        $this->concern->update(['created_at' => now()->subDays(2), 'assigned_to' => null]);
        $this->report->update(['assigned_to' => null]);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Room still needs attention'])->assertOk();
        $this->artisan('concerns:auto-resolve-rooms')->assertSuccessful();
        $this->assertSame('Pending', $this->concern->fresh()->status);
        $this->assertNull($this->report->fresh()->resolved_at);
    }

    public function test_reopened_ticket_counts_as_open_in_mis_analytics(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        $request = Request::create('/admin/analytics');
        $request->setUserResolver(fn () => $this->mis);
        $view = app(RoleAnalyticsController::class)->index($request);
        $stats = $view->getData()['operations']->first()['stats'];
        $this->assertSame(1, $stats['total']);
        $this->assertSame(1, $stats['open']);
        $this->assertSame(0, $stats['completed']);
    }

    public function test_reopen_control_is_shown_only_for_original_reporter(): void
    {
        $this->actingAs($this->reporter);
        $data = ['ticket' => $this->concern, 'errors' => new \Illuminate\Support\ViewErrorBag];
        $this->assertStringContainsString('Reopen ticket', view('partials.reopen-ticket', $data)->render());
        $this->actingAs($this->mis);
        $this->assertStringNotContainsString('Reopen ticket', view('partials.reopen-ticket', $data)->render());
    }

    public function test_guest_cannot_reopen(): void
    {
        $this->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertUnauthorized();
        $this->assertSame('Resolved', $this->concern->fresh()->status);
    }

    public function test_report_owner_cannot_bypass_linked_concern_ownership(): void
    {
        $other = $this->user('student');
        $this->report->update(['user_id' => $other->id]);
        $this->actingAs($other)->postJson(route('reports.reopen', $this->report), ['reason' => 'Still broken'])->assertForbidden();
        $this->assertSame('Resolved', $this->concern->fresh()->status);
    }

    public function test_invalid_linked_report_leaves_entire_ticket_unchanged(): void
    {
        $this->report->update(['status' => 'In Progress']);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertUnprocessable();
        $this->assertSame('Resolved', $this->concern->fresh()->status);
        $this->assertNotNull($this->concern->fresh()->resolved_at);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_staff_cannot_reopen_via_old_report_status_routes(): void
    {
        foreach ([route('reports.updateStatus', $this->report), route('admin.updateReportStatus', $this->report)] as $url) {
            $this->actingAs($this->mis)->postJson($url, ['status' => 'In Progress'])->assertUnprocessable();
        }
        $this->assertSame('Resolved', $this->report->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_reopened_ticket_can_be_resolved_again(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        $this->actingAs($this->mis)->postJson(route('reports.updateStatus', $this->report), [
            'status' => 'Resolved', 'resolution_notes' => 'Repaired switch', 'cost' => 200,
        ])->assertOk();
        $this->assertSame('Resolved', $this->concern->fresh()->status);
        $this->assertNotNull($this->report->fresh()->resolved_at);
        $log = ActivityLog::where('action', 'report_reopened')->firstOrFail();
        $this->assertEquals(150, $log->old_values['cost']);
    }

    public function test_operational_roles_can_scroll_to_previous_records_from_both_detail_endpoints(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Cable failed again'])->assertOk();
        foreach ([$this->mis, $this->user('building_admin'), $this->user('admin'), $this->user('superadmin')] as $viewer) {
            foreach ([route('reports.api.show', $this->report), route('concerns.modalData', $this->concern->id)] as $url) {
                $response = $this->actingAs($viewer)->getJson($url)->assertOk();
                $ticket = $response->json('report') ?? $response->json('concern');
                $this->assertStringContainsString('Past records', $ticket['history_html']);
                $this->assertStringContainsString('Replaced cable', $ticket['history_html']);
                $this->assertStringContainsString('Cable failed again', $ticket['history_html']);
                $this->assertStringContainsString('PHP 150.00', $ticket['history_html']);
                $this->assertSame(1, substr_count($ticket['history_html'], 'Previous repair'));
            }
        }
    }

    public function test_assigned_fixer_can_see_history_but_unassigned_fixer_cannot_access_ticket(): void
    {
        $fixer = $this->user('maintenance');
        $this->concern->update(['assigned_to' => $fixer->id]);
        $this->report->update(['assigned_to' => $fixer->id]);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        $response = $this->actingAs($fixer)->getJson(route('reports.api.show', $this->report))->assertOk();
        $this->assertStringContainsString('Replaced cable', $response->json('report.history_html'));
        $this->actingAs($this->user('maintenance'))->getJson(route('reports.api.show', $this->report))->assertForbidden();
    }

    public function test_reporter_does_not_receive_staff_repair_history(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        $this->getJson(route('concerns.modalData', $this->concern->id))->assertOk()->assertJsonPath('concern.history_html', '');
    }

    public function test_history_escapes_user_content_and_excludes_other_tickets(): void
    {
        $this->concern->update(['resolution_notes' => '<script>alert("notes")</script>']);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        ActivityLog::where('action', 'concern_reopened')->update(['metadata' => json_encode(['reason' => '<img src=x onerror=alert(1)>'])]);
        ActivityLog::create(['action' => 'concern_reopened', 'concern_id' => 9999, 'description' => 'Another private ticket', 'old_values' => ['resolution_notes' => 'Unrelated repair']]);
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->concern->fresh(), $this->mis);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('Unrelated repair', $html);
    }

    public function test_repeated_repairs_remain_in_history_instead_of_being_overwritten(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'First recurrence'])->assertOk();
        foreach ([$this->concern, $this->report] as $ticket) {
            $ticket->refresh()->update(['status' => 'Resolved', 'resolved_at' => now(), 'resolution_notes' => 'Replaced switch', 'cost' => 200]);
        }
        $this->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Second recurrence'])->assertOk();
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->report->fresh(), $this->mis);
        $this->assertStringContainsString('Replaced cable', $html);
        $this->assertStringContainsString('Replaced switch', $html);
        $this->assertSame(2, substr_count($html, 'Previous repair'));
        $this->assertLessThan(strpos($html, 'First recurrence'), strpos($html, 'Second recurrence'));
    }

    public function test_standalone_report_history_uses_its_own_records(): void
    {
        $this->report->update(['concern_id' => null]);
        $this->actingAs($this->reporter)->postJson(route('reports.reopen', $this->report), ['reason' => 'Standalone recurrence'])->assertOk();
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->report->fresh(), $this->mis);
        $this->assertStringContainsString('Standalone recurrence', $html);
        $this->assertStringContainsString('Replaced cable', $html);
    }

    public function test_resolved_ticket_shows_latest_repair_even_before_its_first_reopening(): void
    {
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->concern, $this->mis);
        $this->assertStringContainsString('Latest resolution', $html);
        $this->assertStringContainsString('Replaced cable', $html);
        $this->assertStringNotContainsString('Reason for reopening:', $html);
    }

    private function user(string $role): User
    {
        return User::create(['name' => $role, 'email' => uniqid().'@example.com', 'password' => 'password', 'role' => $role]);
    }
}
