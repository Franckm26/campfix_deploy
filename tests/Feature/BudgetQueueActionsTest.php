<?php

namespace Tests\Feature;

use App\Http\Controllers\BudgetApprovalController;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use App\Support\BudgetApprovalSchema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BudgetQueueActionsTest extends TestCase
{
    private User $schoolAdmin;

    private Report $report;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropAllTables();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->string('password')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('concerns', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('concern_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('severity')->default('low');
            $table->string('status')->default('Resolved');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('school_admin_archived')->default(false);
            $table->boolean('school_admin_deleted')->default(false);
            $table->unsignedBigInteger('archive_folder_id')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('user_archived_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('report_id');
            $table->timestamp('archived_at')->nullable();
            $table->string('archive_folder_name')->nullable();
            $table->timestamps();
        });
        Schema::create('archive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('item_count')->default(0);
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
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
        BudgetApprovalSchema::ensure();
        $this->schoolAdmin = $this->makeUser('school_admin');
        $category = Category::create(['name' => 'Maintenance']);
        $this->report = Report::create([
            'user_id' => $this->makeUser('faculty')->id, 'category_id' => $category->id,
            'title' => 'Air conditioner repair', 'description' => 'Room is too hot', 'location' => 'Room 303',
            'status' => 'Resolved', 'budget_amount' => 500, 'budget_status' => 'approved',
            'budget_requested_by' => $this->schoolAdmin->id, 'budget_requested_at' => now(),
            'budget_reviewed_by' => $this->schoolAdmin->id, 'budget_reviewed_at' => now(),
        ]);
        $this->actingAs($this->schoolAdmin);
    }

    public function test_view_reuses_report_modal_and_includes_budget_information(): void
    {
        $this->get(route('school-admin.budget-approvals.details', $this->report->id), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Report Details')->assertSee('Budget request')
            ->assertSee('PHP 500.00')->assertSee('Room 303')->assertSee('Room is too hot');
    }

    public function test_archiving_uses_report_archives_and_restore_returns_item_to_active_queue(): void
    {
        $this->postJson(route('reports.archive', $this->report))->assertOk()->assertJsonPath('success', true);
        $this->assertCount(0, $this->queue());
        $this->assertSame([$this->report->id], $this->queue('archives')->pluck('id')->all());
        $this->postJson(route('reports.restore', $this->report))->assertOk()->assertJsonPath('success', true);
        $this->assertSame([$this->report->id], $this->queue()->pluck('id')->all());
        $this->assertSame('approved', $this->report->fresh()->budget_status);
    }

    public function test_deleting_moves_to_deleted_and_can_be_restored_without_losing_budget(): void
    {
        $this->deleteJson(route('reports.destroy', $this->report))->assertOk()->assertJsonPath('success', true);
        $this->assertCount(0, $this->queue());
        $this->assertSame([$this->report->id], $this->queue('deleted')->pluck('id')->all());
        $this->get(route('school-admin.budget-approvals.details', $this->report->id), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertSee('PHP 500.00');
        $this->postJson(route('school-admin.budget-approvals.restore-deleted', $this->report->id))->assertOk()->assertJsonPath('success', true);
        $this->assertSame([$this->report->id], $this->queue()->pluck('id')->all());
        $this->assertFalse($this->report->fresh()->school_admin_deleted);
        $this->assertSame('approved', $this->report->fresh()->budget_status);
    }

    public function test_assigned_unresolved_report_cannot_be_deleted(): void
    {
        $this->report->update(['status' => 'In Progress', 'assigned_to' => $this->schoolAdmin->id]);
        $this->deleteJson(route('reports.destroy', $this->report))->assertJsonPath('success', false);
        $this->assertFalse($this->report->fresh()->is_deleted);
        $this->assertCount(1, $this->queue());
    }

    public function test_archive_for_another_user_does_not_hide_item_from_current_queue(): void
    {
        $other = $this->makeUser('school_admin');
        $this->report->archivedByUsers()->attach($other->id);
        $this->assertCount(1, $this->queue());
        $this->assertCount(0, $this->queue('archives'));
    }

    public function test_school_admin_routes_reject_other_roles_and_non_budget_reports(): void
    {
        $this->actingAs($this->makeUser('mis'));
        $this->getJson(route('school-admin.budget-approvals.details', $this->report->id))->assertForbidden();
        $this->postJson(route('school-admin.budget-approvals.restore-deleted', $this->report->id))->assertForbidden();
        $this->actingAs($this->schoolAdmin);
        $this->report->update(['budget_status' => null]);
        $this->getJson(route('school-admin.budget-approvals.details', $this->report->id))->assertNotFound();
        $this->postJson(route('school-admin.budget-approvals.restore-deleted', $this->report->id))->assertNotFound();
    }

    public function test_search_and_status_filters_still_apply_to_archived_budgets(): void
    {
        $this->postJson(route('reports.archive', $this->report))->assertOk();
        $this->assertCount(1, $this->queue('archives', ['status' => 'approved', 'search' => 'air conditioner']));
        $this->assertCount(0, $this->queue('archives', ['status' => 'pending']));
        $this->assertCount(0, $this->queue('archives', ['search' => 'another room']));
    }

    public function test_a_revised_budget_reappears_for_review_after_the_previous_request_was_archived(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $this->report->update(['budget_status' => 'rejected', 'status' => 'In Progress']);
        $this->postJson(route('reports.archive', $this->report))->assertOk();
        $this->assertCount(0, $this->queue());
        $this->actingAs($this->makeUser('building_admin'))->postJson(route('reports.budget.request', $this->report), ['budget_amount' => 600])
            ->assertOk()->assertJsonPath('budget_status', 'pending');
        $this->actingAs($this->schoolAdmin);
        $this->assertCount(1, $this->queue());
        $this->assertFalse($this->report->fresh()->school_admin_archived);
    }

    private function queue(string $view = 'active', array $filters = [])
    {
        $request = Request::create('/school-admin/budget-approvals', 'GET', array_merge(['view' => $view], $filters));

        return app(BudgetApprovalController::class)->index($request)->getData()['budgets']->getCollection();
    }

    private function makeUser(string $role): User
    {
        return User::create(['name' => $role, 'email' => uniqid().'@example.com', 'role' => $role]);
    }
}
