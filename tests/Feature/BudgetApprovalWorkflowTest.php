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

        $migration = require database_path('migrations/2026_09_27_000001_add_budget_approval_to_reports_and_concerns.php');
        $migration->up();
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
        $blocked = app(ReportController::class)->updateStatus(
            $this->jsonRequest(['status' => 'In Progress']),
            $report->id
        );
        $this->assertSame(422, $blocked->getStatusCode());
        $this->assertTrue($blocked->getData(true)['requires_budget_approval']);

        $resolvedWithoutApproval = app(ReportController::class)->updateStatus(
            $this->jsonRequest(['status' => 'Resolved']),
            $report->id
        );
        $this->assertSame(422, $resolvedWithoutApproval->getStatusCode());

        $requested = app(BudgetApprovalController::class)->request(
            $this->jsonRequest(['budget_amount' => 12500]),
            $report
        );
        $this->assertSame(200, $requested->getStatusCode());
        $this->assertSame(Report::BUDGET_PENDING, $report->refresh()->budget_status);
        $this->assertSame(Report::BUDGET_PENDING, $concern->refresh()->budget_status);

        Auth::login($schoolAdministrator);
        $approved = app(BudgetApprovalController::class)->approve($report->refresh());
        $this->assertSame(200, $approved->getStatusCode());
        $this->assertTrue($report->refresh()->hasApprovedBudget());
        $this->assertTrue($concern->refresh()->hasApprovedBudget());
        $this->assertSame($schoolAdministrator->id, $report->budget_reviewed_by);
    }

    private function jsonRequest(array $data): Request
    {
        $request = Request::create('/test', 'POST', $data);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => Auth::user());

        return $request;
    }
}
