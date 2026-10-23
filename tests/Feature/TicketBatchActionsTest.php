<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Concern;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TicketBatchActionsTest extends TestCase
{
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropAllTables();
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid'); $table->string('name'); $table->string('email');
            $table->string('password')->nullable(); $table->string('role');
            $table->boolean('is_deleted')->default(false); $table->boolean('is_archived')->default(false); $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->timestamps();
        });
        foreach (['concerns', 'reports'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id(); $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('category_id')->nullable();
                $table->string('title'); $table->string('status')->default('Pending');
                $table->unsignedBigInteger('assigned_to')->nullable(); $table->string('image_path')->nullable();
                $table->boolean('is_deleted')->default(false); $table->boolean('is_archived')->default(false);
                foreach (['student', 'mis', 'school_admin', 'building_admin'] as $role) {
                    $table->boolean($role.'_archived')->default(false); $table->boolean($role.'_deleted')->default(false);
                }
                $table->unsignedBigInteger('deleted_by')->nullable(); $table->unsignedBigInteger('archive_folder_id')->nullable();
                $table->softDeletes(); $table->timestamps();
                if ($name === 'reports') $table->unsignedBigInteger('concern_id')->nullable();
            });
        }
        foreach (['concerns' => 'concern_id', 'reports' => 'report_id'] as $resource => $column) {
            Schema::create('user_archived_'.$resource, function (Blueprint $table) use ($column) {
                $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger($column);
                $table->timestamp('archived_at')->nullable(); $table->string('archive_folder_name')->nullable(); $table->timestamps();
            });
        }
        Schema::create('archive_folders', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('type'); $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); $table->integer('item_count')->default(0); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable(); $table->unsignedBigInteger('concern_id')->nullable(); $table->unsignedBigInteger('report_id')->nullable();
            $table->string('action'); $table->text('description'); $table->string('ip_address')->nullable(); $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->json('metadata')->nullable(); $table->timestamps();
        });
        $this->owner = $this->user('student');
        $this->actingAs($this->owner);
    }

    private function user(string $role): User
    {
        return User::forceCreate(['name' => 'Test User', 'email' => uniqid().'@example.com', 'password' => 'unused', 'role' => $role]);
    }

    private function ticket(string $resource, array $attributes = [])
    {
        $model = $resource === 'reports' ? Report::class : Concern::class;
        return $model::create(array_merge(['title' => 'Test issue', 'user_id' => $this->owner->id, 'status' => 'Pending'], $attributes));
    }

    private function batch(string $resource, string $action, array $ids)
    {
        return $this->postJson(route('tickets.batch', compact('resource', 'action')), ['ids' => $ids]);
    }

    public function test_report_ids_never_archive_a_concern_with_the_same_id(): void
    {
        $concern = $this->ticket('concerns'); $report = $this->ticket('reports');
        $this->assertSame($concern->id, $report->id);
        $actor = $this->user('school_admin'); $this->actingAs($actor);
        $this->batch('reports', 'archive', [$report->id])->assertOk()->assertJsonPath('count', 1);
        $this->assertTrue($report->fresh()->isArchivedByUser($actor->id));
        $this->assertFalse($concern->fresh()->isArchivedByUser($actor->id));
        $this->batch('reports', 'restore', [$report->id])->assertOk();
        $this->assertFalse($report->fresh()->isArchivedByUser($actor->id));
        $this->assertFalse((bool) $report->fresh()->school_admin_archived);
    }

    public function test_my_concerns_archive_and_restore_only_the_current_users_archive(): void
    {
        $ticket = $this->ticket('concerns');
        $other = $this->user('student'); $ticket->archivedByUsers()->attach($other->id);
        $this->batch('concerns', 'archive', [$ticket->id])->assertOk();
        $this->assertTrue($ticket->fresh()->isArchivedByUser($this->owner->id));
        $this->batch('concerns', 'restore', [$ticket->id])->assertOk();
        $this->assertFalse($ticket->fresh()->isArchivedByUser($this->owner->id));
        $this->assertTrue($ticket->fresh()->isArchivedByUser($other->id));
    }

    public function test_another_users_concern_cannot_be_changed(): void
    {
        $ticket = $this->ticket('concerns', ['user_id' => $this->user('student')->id]);
        $this->batch('concerns', 'archive', [$ticket->id])->assertUnprocessable()->assertJsonPath('success', false);
        $this->assertFalse($ticket->fresh()->isArchivedByUser($this->owner->id));
    }

    public function test_assigned_unresolved_tickets_are_skipped_and_success_count_is_accurate(): void
    {
        $pending = $this->ticket('concerns');
        $assigned = $this->ticket('concerns', ['assigned_to' => 99]);
        $this->batch('concerns', 'soft-delete', [$pending->id, $assigned->id])->assertOk()->assertJsonPath('count', 1)->assertJsonPath('skipped', 1);
        $this->assertTrue((bool) $pending->fresh()->is_deleted);
        $this->assertFalse((bool) $assigned->fresh()->is_deleted);
        $this->batch('concerns', 'restore-deleted', [$pending->id])->assertOk();
        $this->assertFalse((bool) $pending->fresh()->is_deleted);
    }

    public function test_report_delete_restore_and_permanent_delete_use_report_records(): void
    {
        $report = $this->ticket('reports'); $concern = $this->ticket('concerns');
        $this->actingAs($this->user('school_admin'));
        $this->batch('reports', 'soft-delete', [$report->id])->assertOk();
        $this->assertTrue((bool) $report->fresh()->is_deleted);
        $this->assertFalse((bool) $concern->fresh()->is_deleted);
        $this->batch('reports', 'restore-deleted', [$report->id])->assertOk();
        $this->assertFalse((bool) $report->fresh()->is_deleted);
        $this->batch('reports', 'soft-delete', [$report->id])->assertOk();
        $this->batch('reports', 'permanent-delete', [$report->id])->assertOk();
        $this->assertNull(Report::withTrashed()->find($report->id));
        $this->assertNotNull($concern->fresh());
    }

    public function test_active_records_cannot_be_permanently_deleted(): void
    {
        $ticket = $this->ticket('concerns');
        $this->batch('concerns', 'permanent-delete', [$ticket->id])->assertUnprocessable();
        $this->assertNotNull($ticket->fresh());
    }

    public function test_deleted_concern_can_be_permanently_deleted_by_its_owner(): void
    {
        $ticket = $this->ticket('concerns', ['is_deleted' => true]);
        $this->batch('concerns', 'permanent-delete', [$ticket->id])->assertOk()->assertJsonPath('count', 1);
        $this->assertNull(Concern::find($ticket->id));
    }

    public function test_report_batches_keep_operational_category_permissions(): void
    {
        $category = Category::create(['name' => 'Maintenance']);
        $report = $this->ticket('reports', ['category_id' => $category->id]);
        $this->actingAs($this->user('mis'));
        $this->batch('reports', 'archive', [$report->id])->assertUnprocessable();
        $this->assertFalse((bool) $report->fresh()->mis_archived);
    }

    public function test_empty_and_duplicate_selections_are_rejected(): void
    {
        $ticket = $this->ticket('concerns');
        $this->batch('concerns', 'archive', [])->assertUnprocessable();
        $this->batch('concerns', 'archive', [$ticket->id, $ticket->id])->assertUnprocessable();
        $this->assertFalse($ticket->fresh()->isArchivedByUser($this->owner->id));
    }
}
