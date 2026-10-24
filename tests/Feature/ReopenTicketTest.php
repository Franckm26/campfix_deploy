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
                $table->text('details')->nullable();
                $table->string('reporter_name')->nullable();
                $table->boolean('is_anonymous')->default(false);
                $table->unsignedInteger('report_count')->default(1);
                $table->string('image_path')->nullable();
                $table->string('photo_path')->nullable();
                $table->decimal('budget_amount')->nullable();
                $table->string('budget_status')->nullable();
                $table->unsignedBigInteger('budget_requested_by')->nullable();
                $table->unsignedBigInteger('budget_reviewed_by')->nullable();
                $table->timestamp('budget_requested_at')->nullable();
                $table->timestamp('budget_reviewed_at')->nullable();
                $table->text('budget_rejection_reason')->nullable();
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

    public function test_concern_image_replacement_rejects_script_uploads_without_changing_the_record(): void
    {
        Schema::table('categories', fn (Blueprint $table) => $table->json('issues')->nullable());
        $this->actingAs($this->reporter);
        $request = Request::create('/concerns/'.$this->concern->id, 'POST', [
            'location' => $this->concern->location,
            'category_id' => $this->concern->category_id,
            'description' => 'Existing concern',
        ], [], ['image' => \Illuminate\Http\UploadedFile::fake()->createWithContent('bad.php', '<?php echo 1;')]);
        try {
            app(\App\Http\Controllers\ConcernController::class)->update($request, $this->concern->id);
            $this->fail('Script upload was accepted.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
        }
        $this->assertNull($this->concern->fresh()->image_path);
    }

    public function test_new_concern_upload_validation_is_not_converted_to_a_server_error(): void
    {
        Schema::create('user_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_type');
            $table->integer('count')->default(0);
            $table->date('date');
            $table->timestamps();
        });
        $this->actingAs($this->reporter);
        $request = Request::create('/concerns', 'POST', [
            'location' => 'Room 301', 'category_id' => $this->concern->category_id,
        ], [], ['image' => \Illuminate\Http\UploadedFile::fake()->createWithContent('bad.jpg', '<?php echo 1;')]);
        $request->headers->set('Accept', 'application/json');
        try {
            app(\App\Http\Controllers\ConcernController::class)->store($request);
            $this->fail('Invalid image did not produce a validation error.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
            $this->assertSame(422, $exception->status);
        }
        $this->assertSame(1, Concern::count());
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

    private function recurrenceAnalytics(): array
    {
        $viewer = $this->user('admin');
        $this->actingAs($viewer);
        $request = Request::create('/admin/analytics');
        $request->setUserResolver(fn () => $viewer);
        return app(\App\Http\Controllers\AdminController::class)->analytics($request)->getData();
    }

    private function recordReplacement(?User $actor = null, ?string $operationId = null, ?Report $report = null)
    {
        return $this->actingAs($actor ?? $this->mis)->postJson(route('tickets.assetReplacement', [
            'resource' => 'reports', 'id' => ($report ?? $this->report)->id,
        ]), ['replacement_details' => 'New network device, asset tag NEW-304', 'replacement_notes' => 'Old unit replaced and installation tested.',
            'confirmed_replacement' => true, 'operation_id' => $operationId ?? (string) \Illuminate\Support\Str::uuid()]);
    }

    private function replacementResolutionPayload(): array
    {
        return ['status' => 'Resolved', 'cost' => 500, 'resolution_notes' => 'Installed and tested new unit.',
            'asset_replaced' => true, 'replacement_details' => 'New unit, asset tag NEW-304',
            'replacement_notes' => 'Complete unit replacement and installation tested.',
            'confirmed_replacement' => true, 'operation_id' => (string) \Illuminate\Support\Str::uuid()];
    }

    private function startReplacementResolution(): void
    {
        foreach ([$this->concern, $this->report] as $ticket) $ticket->update(['status' => 'In Progress', 'resolved_at' => null]);
    }

    public function test_replacement_can_be_recorded_while_resolving_and_a_retry_does_not_duplicate_it(): void
    {
        $this->startReplacementResolution();
        $payload = $this->replacementResolutionPayload();
        $response = $this->actingAs($this->mis)->postJson(route('reports.updateStatus', $this->report->id), $payload)->assertOk();
        $this->assertSame('Resolved', $this->report->fresh()->status);
        $this->assertSame('Resolved', $this->concern->fresh()->status);
        $this->assertEquals(500, $this->report->fresh()->cost);
        $log = ActivityLog::where('action', 'asset_replaced')->sole();
        $this->assertSame($payload['replacement_details'], $log->metadata['replacement_details']);
        $this->assertSame('Resolved', $log->old_values['status']);
        $this->actingAs($this->mis)->postJson(route('reports.updateStatus', $this->report->id), $payload)
            ->assertOk()->assertJsonPath('replacement_id', $response->json('replacement_id'));
        $this->assertSame(1, ActivityLog::where('action', 'asset_replaced')->count());
    }

    public function test_replacement_resolution_requires_details_notes_and_confirmation_before_saving(): void
    {
        $this->startReplacementResolution();
        $payload = $this->replacementResolutionPayload();
        unset($payload['replacement_details'], $payload['replacement_notes'], $payload['confirmed_replacement']);
        $this->actingAs($this->mis)->postJson(route('reports.updateStatus', $this->report->id), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors(['replacement_details', 'replacement_notes', 'confirmed_replacement']);
        $this->assertSame('In Progress', $this->report->fresh()->status);
        $this->assertSame(0, ActivityLog::where('action', 'asset_replaced')->count());
    }

    public function test_failed_replacement_authorization_rolls_back_report_and_concern_resolution(): void
    {
        $this->startReplacementResolution();
        $before = $this->report->fresh()->getAttributes();
        $this->actingAs($this->user('building_admin'))->postJson(route('reports.updateStatus', $this->report->id), $this->replacementResolutionPayload())->assertForbidden();
        $this->assertSame($before, $this->report->fresh()->getAttributes());
        $this->assertSame('In Progress', $this->concern->fresh()->status);
        $this->assertSame(0, ActivityLog::where('action', 'asset_replaced')->count());
    }

    public function test_replacing_a_part_during_resolution_does_not_reset_asset_recurrence(): void
    {
        $this->startReplacementResolution();
        $this->actingAs($this->mis)->postJson(route('reports.updateStatus', $this->report->id), [
            'status' => 'Resolved', 'replaced_part' => 'Cable', 'resolution_notes' => 'Replaced only the damaged cable.',
        ])->assertOk();
        $this->assertSame('Resolved', $this->report->fresh()->status);
        $this->assertSame(0, ActivityLog::where('action', 'asset_replaced')->count());
    }

    public function test_pending_budget_still_blocks_resolution_with_a_replacement(): void
    {
        $this->startReplacementResolution();
        $this->report->update(['budget_status' => 'pending', 'budget_amount' => 500]);
        $this->actingAs($this->mis)->postJson(route('reports.updateStatus', $this->report->id), $this->replacementResolutionPayload())
            ->assertUnprocessable()->assertJsonPath('requires_budget_approval', true);
        $this->assertSame('In Progress', $this->report->fresh()->status);
        $this->assertSame(0, ActivityLog::where('action', 'asset_replaced')->count());
    }

    public function test_budget_can_be_requested_before_starting_work_but_pending_or_approved_requests_cannot_be_overwritten(): void
    {
        foreach ([$this->concern, $this->report] as $ticket) $ticket->update(['status' => 'Assigned', 'resolved_at' => null]);
        $this->actingAs($this->mis)->getJson(route('admin.reports.modalData', $this->report))
            ->assertOk()->assertJsonPath('report.can_request_budget', true);
        $this->actingAs($this->mis)->postJson(route('reports.budget.request', $this->report), ['budget_amount' => 500])
            ->assertOk()->assertJsonPath('budget_status', 'pending');
        $this->actingAs($this->mis)->getJson(route('admin.reports.modalData', $this->report))
            ->assertOk()->assertJsonPath('report.can_request_budget', false);
        $this->assertSame('Assigned', $this->report->fresh()->status);
        $this->assertSame('pending', $this->concern->fresh()->budget_status);
        $this->actingAs($this->mis)->postJson(route('reports.budget.request', $this->report), ['budget_amount' => 999])->assertUnprocessable();
        $this->assertEquals(500, $this->report->fresh()->budget_amount);
        $this->assertSame(1, ActivityLog::where('action', 'budget_approval_requested')->count());

        foreach ([$this->concern, $this->report] as $ticket) $ticket->update(['budget_status' => 'approved']);
        $this->actingAs($this->mis)->postJson(route('reports.budget.request', $this->report), ['budget_amount' => 999])->assertUnprocessable();
        $this->assertSame('approved', $this->report->fresh()->budget_status);

        foreach ([$this->concern, $this->report] as $ticket) $ticket->update(['budget_status' => 'rejected']);
        $this->actingAs($this->mis)->postJson(route('reports.budget.request', $this->report), ['budget_amount' => 400])
            ->assertOk()->assertJsonPath('budget_status', 'pending');
        $this->assertEquals(400, $this->report->fresh()->budget_amount);
        $this->assertEquals(400, $this->concern->fresh()->budget_amount);
    }

    private function reopenAndComplete(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        foreach ([$this->concern->fresh(), $this->report->fresh()] as $ticket) {
            $ticket->update(['status' => 'Resolved', 'resolved_at' => now()]);
        }
    }

    public function test_recording_replacement_clears_old_recurrence_but_preserves_ticket_history(): void
    {
        $this->reopenAndComplete();
        Report::create($this->report->only(['user_id', 'category_id', 'title', 'location', 'status', 'resolved_at', 'cost']));
        $this->assertCount(1, $this->recurrenceAnalytics()['recurringIssueStats']);
        $this->recordReplacement()->assertOk()->assertJsonPath('success', true);
        $this->assertCount(0, $this->recurrenceAnalytics()['recurringIssueStats']);
        $this->assertSame(2, ActivityLog::whereIn('action', ['concern_reopened', 'report_reopened'])->count());
        $this->assertEquals(150, $this->report->fresh()->cost);
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->report->fresh(), $this->mis);
        $this->assertStringContainsString('Previous repair', $html);
        $this->assertStringContainsString('New network device, asset tag NEW-304', $html);
        $this->assertStringContainsString('Asset replaced', $html);
    }

    public function test_new_asset_first_failure_does_not_inherit_old_repair_cycles(): void
    {
        $this->reopenAndComplete();
        $this->recordReplacement()->assertOk();
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'New unit failed'])->assertOk();
        $this->assertCount(0, $this->recurrenceAnalytics()['recurringIssueStats']);
        foreach ([$this->concern->fresh(), $this->report->fresh()] as $ticket) {
            $ticket->update(['status' => 'Resolved', 'resolved_at' => now()]);
        }
        $this->assertCount(0, $this->recurrenceAnalytics()['recurringIssueStats']);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'New unit failed again'])->assertOk();
        $issue = $this->recurrenceAnalytics()['recurringIssueStats']->first();
        $this->assertSame(1, $issue['repair_cycles']);
        $this->assertSame(1, $issue['reopenings']);
        $this->assertSame(2, $issue['count']);
    }

    public function test_new_tickets_in_the_same_location_start_with_the_new_asset_history_only(): void
    {
        $this->reopenAndComplete();
        $this->recordReplacement()->assertOk();
        $new = Report::create($this->report->only(['user_id', 'category_id', 'title', 'location', 'status', 'resolved_at', 'cost']));
        $this->assertCount(0, $this->recurrenceAnalytics()['recurringIssueStats']);
        $this->actingAs($this->reporter)->postJson(route('reports.reopen', $new), ['reason' => 'New asset issue returned'])->assertOk();
        $issue = $this->recurrenceAnalytics()['recurringIssueStats']->first();
        $this->assertSame(1, $issue['repair_cycles']);
        $this->assertSame(2, $issue['count']);
    }

    public function test_replacement_does_not_clear_other_locations_or_other_issues(): void
    {
        $this->reopenAndComplete();
        foreach (['Room 888', 'Room 999'] as $location) {
            for ($i = 0; $i < 2; $i++) Report::create(array_merge($this->report->only(['user_id', 'category_id', 'title', 'status', 'resolved_at', 'cost']), ['location' => $location]));
        }
        for ($i = 0; $i < 2; $i++) Report::create(array_merge($this->report->only(['user_id', 'category_id', 'location', 'status', 'resolved_at', 'cost']), ['title' => 'Different issue']));
        $this->recordReplacement()->assertOk();
        $issues = $this->recurrenceAnalytics()['recurringIssueStats'];
        $this->assertCount(3, $issues);
        $this->assertSame(['Different issue'], $issues->where('location', $this->report->location)->pluck('issue')->all());
        $this->assertSame(['Room 888', 'Room 999'], $issues->where('issue', $this->report->title)->pluck('location')->sort()->values()->all());
    }

    public function test_replacement_is_staff_only_requires_resolution_and_is_idempotent(): void
    {
        $this->recordReplacement($this->reporter)->assertForbidden();
        $other = $this->user('building_admin');
        $this->recordReplacement($other)->assertForbidden();
        $this->report->update(['status' => 'Assigned']);
        $this->recordReplacement()->assertForbidden();
        $this->report->update(['status' => 'Resolved']);
        $operation = (string) \Illuminate\Support\Str::uuid();
        $first = $this->recordReplacement(null, $operation)->assertOk()->json('replacement_id');
        $this->assertSame($first, $this->recordReplacement(null, $operation)->assertOk()->json('replacement_id'));
        $this->assertSame(1, ActivityLog::where('action', 'asset_replaced')->count());
    }

    public function test_mis_recurrence_alert_also_resets_after_replacement(): void
    {
        $this->reopenAndComplete();
        $this->recordReplacement()->assertOk();
        $this->actingAs($this->mis);
        $request = Request::create('/analytics');
        $request->setUserResolver(fn () => $this->mis);
        $alerts = app(RoleAnalyticsController::class)->index($request)->getData()['decisionAlerts'];
        $this->assertNotContains('Recurring MIS issues', $alerts->pluck('title')->all());
    }

    public function test_replacement_requires_details_notes_and_explicit_confirmation(): void
    {
        $this->actingAs($this->mis)->postJson(route('tickets.assetReplacement', ['resource' => 'reports', 'id' => $this->report->id]), [
            'replacement_details' => ' ', 'replacement_notes' => '', 'operation_id' => (string) \Illuminate\Support\Str::uuid(),
        ])->assertUnprocessable()->assertJsonValidationErrors(['replacement_details', 'replacement_notes', 'confirmed_replacement']);
        $this->assertSame(0, ActivityLog::where('action', 'asset_replaced')->count());
    }

    public function test_a_later_replacement_starts_another_fresh_asset_generation(): void
    {
        $this->reopenAndComplete();
        $this->recordReplacement()->assertOk();
        $this->reopenAndComplete();
        $this->reopenAndComplete();
        $this->assertCount(1, $this->recurrenceAnalytics()['recurringIssueStats']);
        $this->recordReplacement()->assertOk();
        $this->assertCount(0, $this->recurrenceAnalytics()['recurringIssueStats']);
        $this->assertSame(2, ActivityLog::where('action', 'asset_replaced')->count());
        $this->assertSame(3, ActivityLog::where('action', 'concern_reopened')->count());
    }

    public function test_replacement_history_escapes_staff_entered_details(): void
    {
        $this->actingAs($this->mis)->postJson(route('tickets.assetReplacement', ['resource' => 'reports', 'id' => $this->report->id]), [
            'replacement_details' => '<script>alert(1)</script>', 'replacement_notes' => 'Complete replacement verified.',
            'confirmed_replacement' => true, 'operation_id' => (string) \Illuminate\Support\Str::uuid(),
        ])->assertOk();
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->report->fresh(), $this->mis);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_first_reopening_triggers_a_recurring_repair_decision_alert(): void
    {
        $this->assertCount(0, $this->recurrenceAnalytics()['recurringIssueStats']);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        $data = $this->recurrenceAnalytics();
        $issue = $data['recurringIssueStats']->first();
        $this->assertSame(1, $issue['reopenings']);
        $this->assertSame(1, $issue['repair_cycles']);
        $this->assertSame(2, $issue['count']);
        $this->assertEquals(150, $issue['cost']);
        $this->assertSame(1, $data['totalReports']);
        $alert = $data['decisionAlerts']->first(fn ($alert) => str_starts_with($alert['key'], 'recurring-repair-'));
        $this->assertSame('warning', $alert['level']);
        $this->assertSame(1, $alert['stats']['Reopenings']);
    }

    public function test_repeated_repair_cycles_count_history_without_double_counting_paired_logs(): void
    {
        for ($cycle = 0; $cycle < 3; $cycle++) {
            if ($cycle > 0) {
                foreach ([$this->concern->fresh(), $this->report->fresh()] as $ticket) {
                    $ticket->update(['status' => 'Resolved', 'resolved_at' => now()]);
                }
            }
            $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        }
        $this->assertSame(6, ActivityLog::whereIn('action', ['concern_reopened', 'report_reopened'])->count());
        $data = $this->recurrenceAnalytics();
        $issue = $data['recurringIssueStats']->first();
        $this->assertSame(3, $issue['reopenings']);
        $this->assertSame(3, $issue['repair_cycles']);
        $this->assertSame(4, $issue['count']);
        $this->assertSame(3, array_sum(array_column($issue['trend'], 'reports')) - 1);
        $this->assertSame('critical', $data['decisionAlerts']->first()['level']);
        ActivityLog::where('action', 'report_reopened')->orderBy('id')->first()->delete();
        $this->assertSame(3, $this->recurrenceAnalytics()['recurringIssueStats']->first()['reopenings']);
    }

    public function test_latest_completed_repair_is_added_to_the_saved_repair_cycles(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        foreach ([$this->concern->fresh(), $this->report->fresh()] as $ticket) {
            $ticket->update(['status' => 'Resolved', 'resolved_at' => now()]);
        }
        $issue = $this->recurrenceAnalytics()['recurringIssueStats']->first();
        $this->assertSame(2, $issue['repair_cycles']);
        $this->assertSame(1, $issue['reopenings']);
    }

    public function test_concern_history_is_used_when_the_report_history_is_missing(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        ActivityLog::where('action', 'report_reopened')->delete();
        $issue = $this->recurrenceAnalytics()['recurringIssueStats']->first();
        $this->assertSame(1, $issue['repair_cycles']);
        $this->assertSame(1, $issue['reopenings']);
    }

    public function test_paired_legacy_snapshots_with_a_missing_resolution_date_count_once(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        $log = ActivityLog::where('action', 'report_reopened')->firstOrFail();
        $saved = $log->old_values;
        unset($saved['resolved_at']);
        $log->update(['old_values' => $saved]);
        $issue = $this->recurrenceAnalytics()['recurringIssueStats']->first();
        $this->assertSame(1, $issue['reopenings']);
        $this->assertSame(1, $issue['repair_cycles']);
    }

    public function test_mis_decision_alerts_include_reopened_technology_issues(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        $this->actingAs($this->mis);
        $request = Request::create('/analytics');
        $request->setUserResolver(fn () => $this->mis);
        $data = app(RoleAnalyticsController::class)->index($request)->getData();
        $this->assertContains('Recurring MIS issues', $data['decisionAlerts']->pluck('title')->all());
    }

    public function test_previous_repairs_for_a_different_issue_are_not_counted_against_the_new_issue(): void
    {
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Same issue returned'])->assertOk();
        $this->report->update(['title' => 'Different issue', 'location' => 'Room 999']);
        $this->assertCount(0, $this->recurrenceAnalytics()['recurringIssueStats']);
    }

    public function test_two_separate_completed_reports_still_trigger_the_existing_recurrence_alert(): void
    {
        Report::create($this->report->only(['user_id', 'category_id', 'title', 'location', 'status', 'resolved_at', 'cost']));
        $issue = $this->recurrenceAnalytics()['recurringIssueStats']->first();
        $this->assertSame(2, $issue['repair_cycles']);
        $this->assertSame(0, $issue['reopenings']);
        $this->assertSame(2, $issue['count']);
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
        $histories = app(\App\Services\TicketRecurrenceService::class)->histories(collect([$this->report->fresh()]));
        $this->assertCount(1, $histories->get($this->report->id));
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
                $this->assertStringContainsString('>History</h5>', $ticket['history_html']);
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
        $this->actingAs($this->mis)->getJson(route('reports.api.show', $this->report))->assertOk();
        $this->getJson(route('concerns.modalData', $this->concern->id))->assertOk();
    }

    public function test_history_preserves_the_full_ticket_details_when_later_edits_change_them(): void
    {
        foreach ([$this->concern, $this->report] as $ticket) {
            $ticket->update([
                'description' => 'Original cable problem', 'details' => 'Desk 12 has no internet',
                'priority' => 'high', 'report_count' => 3,
                'budget_amount' => 150, 'budget_status' => 'approved',
                'budget_requested_by' => $this->mis->id, 'budget_reviewed_by' => $this->mis->id,
                'budget_requested_at' => now()->subDays(2), 'budget_reviewed_at' => now()->subDay(),
                'damaged_part' => 'Old network cable', 'replaced_part' => 'CAT6 cable',
            ]);
        }
        $this->concern->update(['image_path' => 'concerns/original-photo.jpg']);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Connection failed again'])->assertOk();
        foreach ([$this->concern, $this->report] as $ticket) {
            $ticket->refresh()->update(['title' => 'Updated title', 'description' => 'New description', 'details' => 'New details', 'location' => 'Room 999', 'priority' => 'low']);
        }
        $this->mis->update(['name' => 'Renamed MIS account']);
        foreach ([$this->concern, $this->report] as $ticket) {
            $html = app(\App\Services\TicketHistoryService::class)->renderFor($ticket->fresh(), $this->mis);
            foreach (['Internet unavailable', 'Original cable problem', 'Desk 12 has no internet', 'Room 301',
                'Technology/Internet', 'CAT6 cable', 'Old network cable', 'High', 'Approved', 'PHP 150.00',
                'Date submitted:', 'Work started:', 'Report count:', 'student', 'original-photo.jpg'] as $text) {
                $this->assertStringContainsString($text, $html);
            }
            $this->assertStringNotContainsString('Updated title', $html);
            $this->assertStringNotContainsString('New description', $html);
            $this->assertStringNotContainsString('Room 999', $html);
            $this->assertStringNotContainsString('Renamed MIS account', $html);
            $this->assertStringNotContainsString('Original ticket details were not recorded', $html);
        }
    }

    public function test_history_uses_the_report_assignment_and_supports_legacy_staff_assignments(): void
    {
        $this->concern->update(['assigned_to' => null]);
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->report->fresh(), $this->mis);
        $this->assertStringContainsString('Assigned to:</strong> mis', $html);
        \App\Models\MaintenanceStaff::create(['name' => 'Legacy technician', 'email' => 'tech@example.com']);
        $staff = \App\Models\MaintenanceStaff::first();
        $this->report->update(['assigned_to' => $staff->id + 100]);
        $staff->id += 100;
        $staff->save();
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->report->fresh(), $this->mis);
        $this->assertStringContainsString('Legacy technician', $html);
    }

    public function test_older_history_entries_show_a_clear_notice_and_the_saved_repair_details(): void
    {
        ActivityLog::create([
            'action' => 'concern_reopened', 'concern_id' => $this->concern->id,
            'description' => 'Old reopening', 'old_values' => ['resolution_notes' => 'Earlier repair', 'assigned_to' => $this->mis->id],
        ]);
        $this->concern->update(['status' => 'Assigned', 'resolution_notes' => null]);
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->concern->fresh(), $this->mis);
        $this->assertStringContainsString('Original ticket details were not recorded', $html);
        $this->assertStringContainsString('Earlier repair', $html);
        $this->assertStringContainsString('Internet unavailable', $html);
    }

    public function test_anonymous_reporter_identity_remains_hidden_in_full_history(): void
    {
        $this->concern->update(['is_anonymous' => true]);
        $this->actingAs($this->reporter)->postJson(route('concerns.reopen', $this->concern), ['reason' => 'Still broken'])->assertOk();
        $html = app(\App\Services\TicketHistoryService::class)->renderFor($this->concern->fresh(), $this->mis);
        $this->assertStringContainsString('Reported by:</strong> Anonymous', $html);
        $this->assertStringNotContainsString($this->reporter->email, $html);
    }

    private function user(string $role): User
    {
        return User::create(['name' => $role, 'email' => uniqid().'@example.com', 'password' => 'password', 'role' => $role]);
    }
}
