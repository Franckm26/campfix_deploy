<?php

namespace Tests\Feature;

use App\Http\Controllers\BudgetApprovalController;
use App\Http\Controllers\ReportController;
use App\Models\Concern;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BudgetApprovalWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
        Schema::create('concerns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->default('Assigned');
            $table->timestamps();
        });
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concern_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->default('Assigned');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->foreignId('report_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

    }

    public function test_school_administrator_approval_is_required_before_progress(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('severity')->default('urgent');
        });
        $buildingAdministrator = User::create([
            'name' => 'Building Administrator',
            'email' => 'building@example.test',
            'role' => 'building_admin',
        ]);
        $schoolAdministrator = User::create([
            'name' => 'School Administrator',
            'email' => 'school@example.test',
            'role' => User::ROLE_SCHOOL_ADMIN,
        ]);
        $concern = Concern::create([
            'user_id' => $buildingAdministrator->id,
            'title' => 'Repair laboratory air conditioner',
            'status' => 'Assigned',
        ]);
        $report = Report::create([
            'user_id' => $buildingAdministrator->id,
            'concern_id' => $concern->id,
            'title' => $concern->title,
            'status' => 'Assigned',
        ]);

        Auth::login($buildingAdministrator);
        $started = app(ReportController::class)->updateStatus(
            $this->jsonRequest(['status' => 'In Progress']),
            $report->id
        );
        $this->assertSame(200, $started->getStatusCode());
        $this->assertSame('In Progress', $report->refresh()->status);
        $this->assertSame('In Progress', $concern->refresh()->status);

        $requested = app(BudgetApprovalController::class)->request(
            $this->jsonRequest(['budget_amount' => 12500]),
            $report->refresh()
        );
        $this->assertSame(200, $requested->getStatusCode());
        $this->assertTrue(Schema::hasColumns('reports', ['budget_amount', 'budget_status', 'budget_requested_by']));
        $this->assertTrue(Schema::hasColumns('concerns', ['budget_amount', 'budget_status', 'budget_requested_by']));
        $this->assertSame(Report::BUDGET_PENDING, $report->refresh()->budget_status);
        $this->assertSame(Report::BUDGET_PENDING, $concern->refresh()->budget_status);

        $blockedResolution = app(ReportController::class)->updateStatus(
            $this->jsonRequest(['status' => 'Resolved']),
            $report->id
        );
        $this->assertSame(422, $blockedResolution->getStatusCode());
        $this->assertTrue($blockedResolution->getData(true)['requires_budget_approval']);

        Auth::login($schoolAdministrator);
        $schoolBlocked = app(ReportController::class)->updateStatus(
            $this->jsonRequest(['status' => 'Resolved']),
            $report->id
        );
        $this->assertSame(422, $schoolBlocked->getStatusCode());
        $this->assertTrue($schoolBlocked->getData(true)['requires_budget_approval']);
        $approved = app(BudgetApprovalController::class)->approve($report->refresh());
        $this->assertSame(200, $approved->getStatusCode());
        $this->assertTrue($report->refresh()->hasApprovedBudget());
        $this->assertTrue($concern->refresh()->hasApprovedBudget());
        $this->assertSame($schoolAdministrator->id, $report->budget_reviewed_by);

        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $reportsView = file_get_contents(resource_path('views/admin/reports.blade.php'));
        $approvalView = file_get_contents(resource_path('views/school-admin/budget-approvals.blade.php'));
        $adminController = file_get_contents(app_path('Http/Controllers/AdminController.php'));
        $route = app('router')->getRoutes()->match(Request::create('/school-admin/budget-approvals', 'GET'));
        $this->assertSame(BudgetApprovalController::class.'@index', $route->getActionName());
        $this->assertStringContainsString("route('school-admin.budget-approvals')", $layout);
        $this->assertStringContainsString('Budget Approvals', $layout);
        $this->assertStringContainsString('Approve budget', $reportsView);
        $this->assertStringContainsString('No budget requests found', $approvalView);
        $this->assertStringContainsString('<h2><i class="fas fa-coins me-2"></i>Budget Approvals</h2>', $approvalView);
        $this->assertStringContainsString('class="nav nav-pills mb-0 flex-wrap"', $approvalView);
        $this->assertStringContainsString('class="card mb-4"', $approvalView);
        $this->assertStringNotContainsString('Review budget requests submitted for concerns already in progress.', $approvalView);
        $this->assertStringNotContainsString('assignReport(', $approvalView);
        $this->assertStringContainsString('Archive budget report', $approvalView);
        $this->assertStringContainsString('View budget report', $approvalView);
        $this->assertStringContainsString('Delete budget report', $approvalView);
        $this->assertStringContainsString("['admin', 'building_admin', 'school_admin', 'academic_head', 'mis']", $adminController);
    }

    public function test_decision_queues_and_priority_notifications_follow_responsibility(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        Schema::table('reports', function (Blueprint $table) {
            $table->string('severity')->nullable();
            $table->boolean('is_safety_hazard')->default(false);
        });
        $school = User::create(['name' => 'School', 'role' => 'school_admin']);
        $system = User::create(['name' => 'System', 'role' => 'admin']);
        $building = User::create(['name' => 'Building', 'role' => 'building_admin']);
        $low = Report::create(['title' => 'Low', 'severity' => 'low']);
        $urgent = Report::create(['title' => 'Urgent', 'severity' => 'urgent']);
        $hazard = Report::create(['title' => 'Hazard', 'severity' => 'low', 'is_safety_hazard' => true]);
        $medium = Report::create(['title' => 'Medium', 'severity' => 'medium']);
        $unclassified = Report::create(['title' => 'Needs triage']);

        $this->assertEqualsCanonicalizing([$urgent->id, $hazard->id], Report::forDecisionQueue($school)->pluck('id')->all());
        $this->assertSame([$low->id], Report::forDecisionQueue($system)->pluck('id')->all());
        $this->assertSame(2, Report::forDecisionQueue($school, 'all')->count());
        $this->assertSame(5, Report::forDecisionQueue($building)->count());
        $school->forceFill(['permissions' => ['events']]);
        $this->assertTrue($school->canAccess('reports'));

        $notification = \App\Notifications\PriorityReviewNotification::class;
        \Illuminate\Support\Facades\Notification::assertSentToTimes($school, $notification, 2);
        \Illuminate\Support\Facades\Notification::assertSentToTimes($system, $notification, 1);
        \Illuminate\Support\Facades\Notification::assertNotSentTo($building, $notification);
        $medium->update(['severity' => 'urgent']);
        $medium->update(['title' => 'Renamed urgent report']);
        \Illuminate\Support\Facades\Notification::assertSentToTimes($school, $notification, 3);
    }

    public function test_school_admin_can_load_assignment_staff_lists(): void
    {
        Schema::create('maintenance_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
        $school = User::create(['name' => 'School', 'role' => 'school_admin']);
        $mis = User::create(['name' => 'MIS', 'role' => 'mis']);
        \Illuminate\Support\Facades\DB::table('maintenance_staff')->insert(['name' => 'Maintenance']);
        Auth::login($school);
        $controller = app(\App\Http\Controllers\AdminController::class);
        $this->assertSame($mis->id, $controller->getMisUsers()->getData(true)['users'][0]['id']);
        $this->assertSame('Maintenance', $controller->getMaintenanceUsers()->getData(true)['users'][0]['name']);
    }

    public function test_school_can_resolve_only_escalated_concerns_and_system_only_low_priority(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        foreach (['reports', 'concerns'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->string($name === 'reports' ? 'severity' : 'priority')->nullable();
                $table->boolean('is_safety_hazard')->default(false);
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->decimal('cost')->nullable();
                $table->string('damaged_part')->nullable();
                $table->string('replaced_part')->nullable();
            });
        }
        Schema::create('report_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('changed_by');
            $table->string('old_status');
            $table->string('new_status');
            $table->timestamp('changed_at');
            $table->timestamps();
        });
        $school = User::create(['name' => 'School', 'role' => 'school_admin']);
        $system = User::create(['name' => 'System', 'role' => 'admin']);
        $controller = app(ReportController::class);

        foreach ([['urgent', false], ['low', true], ['low', false], ['medium', false]] as [$priority, $hazard]) {
            $concern = Concern::create(['title' => 'Repair', 'priority' => $priority, 'is_safety_hazard' => $hazard, 'status' => 'In Progress']);
            $report = Report::create(['concern_id' => $concern->id, 'severity' => $priority, 'is_safety_hazard' => $hazard, 'status' => 'In Progress']);
            if ($priority !== 'urgent' && ! $hazard) {
                Auth::login($school);
                foreach (['In Progress', 'Resolved'] as $status) {
                    $blocked = $controller->updateStatus($this->jsonRequest(['status' => $status]), $report->id);
                    $this->assertSame(403, $blocked->getStatusCode());
                }
                $blockedAssignment = app(\App\Http\Controllers\AdminController::class)->assignReport($this->jsonRequest([]), $report->id);
                $this->assertSame(403, $blockedAssignment->getStatusCode());
            }
            Auth::login($system);
            $result = $controller->updateStatus($this->jsonRequest(['status' => 'Resolved', 'resolution_notes' => 'Completed']), $report->id);
            if ($priority === 'low' && ! $hazard) {
                $this->assertSame(200, $result->getStatusCode());
            } else {
                $this->assertSame(403, $result->getStatusCode());
                $this->assertSame('In Progress', $report->refresh()->status);
                Auth::login($school);
                $result = $controller->updateStatus($this->jsonRequest(['status' => 'Resolved', 'resolution_notes' => 'Covered during absence']), $report->id);
                if ($priority !== 'urgent' && ! $hazard) {
                    $this->assertSame(403, $result->getStatusCode());
                    $this->assertSame('In Progress', $report->refresh()->status);
                    $this->assertSame('In Progress', $concern->refresh()->status);
                    continue;
                }
                $this->assertSame(200, $result->getStatusCode());
            }
            $this->assertSame('Resolved', $report->refresh()->status);
            $this->assertSame('Resolved', $concern->refresh()->status);
            $this->assertNotNull($report->resolved_at);
        }
    }

    private function jsonRequest(array $data): Request
    {
        $request = Request::create('/test', 'POST', $data);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => Auth::user());

        return $request;
    }
}
