<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\User;
use App\Models\UserArchiveFolder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReturningStudentImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->json('permissions')->nullable();
            $table->boolean('is_superadmin')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('archive_folder_id')->nullable();
            $table->timestamps();
        });
        Schema::create('user_archive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('user_count')->default(0);
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('concern_id')->nullable();
            $table->string('action');
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function test_import_restores_returning_students_without_resetting_credentials(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_id')->nullable();
        });
        $actor = User::forceCreate(['name' => 'MIS', 'email' => 'mis@example.com', 'password' => 'unchanged', 'role' => 'mis']);
        $this->actingAs($actor);
        $old = UserArchiveFolder::create(['name' => '2025-2026', 'user_count' => 3]);
        $target = UserArchiveFolder::create(['name' => '2026-2027', 'user_count' => 0]);
        $returning = User::forceCreate([
            'name' => 'Existing Name', 'email' => 'existing@example.com',
            'password' => 'existing-password-hash', 'role' => 'student',
            'student_id' => '02000123456', 'is_archived' => true, 'archive_folder_id' => $old->id,
        ]);
        $absent = User::forceCreate([
            'name' => 'Absent', 'email' => 'absent@example.com', 'password' => 'untouched',
            'role' => 'student', 'student_id' => '02000123457', 'is_archived' => true, 'archive_folder_id' => $old->id,
        ]);
        $deleted = User::forceCreate([
            'name' => 'Deleted', 'email' => 'deleted@example.com', 'password' => 'untouched',
            'role' => 'student', 'student_id' => '02000123458', 'is_archived' => true,
            'is_deleted' => true, 'archive_folder_id' => $old->id,
        ]);
        $csv = "student_id,email,password,role\n02000123456,new@example.com,new-password,student\n02000123456,new@example.com,new-password,student\n02000123458,deleted@example.com,new-password,student\n";
        $request = Request::create('/admin/users/import', 'POST', [
            'default_role' => 'student', 'file_format' => 'standard',
            'archive_folder_name' => '2026-2027',
        ], [], ['file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('students.csv', $csv)]);
        $request->setUserResolver(fn () => $actor);
        $request->setLaravelSession(app('session.store'));
        $response = app(AdminController::class)->importUsers($request);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse((bool) $returning->fresh()->is_archived);
        $this->assertSame($target->id, $returning->fresh()->archive_folder_id);
        $this->assertSame('existing-password-hash', $returning->fresh()->password);
        $this->assertSame('existing@example.com', $returning->fresh()->email);
        $this->assertSame('Existing Name', $returning->fresh()->name);
        $this->assertTrue((bool) $absent->fresh()->is_archived);
        $this->assertTrue((bool) User::withoutGlobalScopes()->find($deleted->id)->is_deleted);
        $this->assertSame(1, $target->fresh()->user_count);
        $this->assertSame(1, $old->fresh()->user_count);
        $this->assertStringContainsString('restored 1 returning students', session('success'));
        // No delivery table is needed: restoration must never insert a welcome email.
        app(AdminController::class)->importUsers($request);
        $this->assertStringContainsString('restored 0 returning students', session('success'));
    }
}
