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
        $approved = app(BudgetApprovalController::class)->approve($report->refresh());
        $this->assertSame(200, $approved->getStatusCode());
        $this->assertTrue($report->refresh()->hasApprovedBudget());
        $this->assertTrue($concern->refresh()->hasApprovedBudget());
        $this->assertSame($schoolAdministrator->id, $report->budget_reviewed_by);

        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $reportsView = file_get_contents(resource_path('views/admin/reports.blade.php'));
        $this->assertStringContainsString("route('school-admin.reports')", $layout);
        $this->assertStringContainsString('Budget Approvals', $layout);
        $this->assertStringContainsString('Approve budget', $reportsView);
    }

    private function jsonRequest(array $data): Request
    {
        $request = Request::create('/test', 'POST', $data);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => Auth::user());

        return $request;
    }
}
