<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\User;
use App\Models\UserArchiveFolder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArchiveAllUsersTest extends TestCase
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

    private function account(string $role, array $attributes = []): User
    {
        return User::forceCreate(array_merge([
            'name' => 'Account',
            'email' => uniqid().'@example.com',
            'password' => 'unused',
            'role' => $role,
        ], $attributes));
    }

    public function test_archives_all_pages_and_roles_but_preserves_excluded_accounts(): void
    {
        $actor = $this->account('mis');
        $this->actingAs($actor);
        $superadmin = $this->account('mis', ['is_superadmin' => true]);
        $deleted = $this->account('student', ['is_deleted' => true]);
        $oldFolder = UserArchiveFolder::create(['name' => 'Old', 'user_count' => 1]);
        $alreadyArchived = $this->account('faculty', ['is_archived' => true, 'archive_folder_id' => $oldFolder->id]);
        foreach (range(1, 45) as $number) {
            $this->account($number % 2 ? 'student' : 'mis');
        }
        $request = Request::create('/admin/users/archive-all', 'POST', ['folder_name' => '2026-2027']);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $actor);
        $response = app(AdminController::class)->archiveAllUsers($request);
        $this->assertSame(45, $response->getData(true)['count']);
        $this->assertFalse((bool) $actor->fresh()->is_archived);
        $this->assertFalse((bool) $superadmin->fresh()->is_archived);
        $this->assertFalse((bool) User::withoutGlobalScopes()->find($deleted->id)->is_archived);
        $this->assertSame($oldFolder->id, $alreadyArchived->fresh()->archive_folder_id);
        $this->assertSame(45, UserArchiveFolder::where('name', '2026-2027')->first()->user_count);
        $this->assertSame(1, $oldFolder->fresh()->user_count);
        $again = app(AdminController::class)->archiveAllUsers($request);
        $this->assertSame(0, $again->getData(true)['count']);
    }

    public function test_requires_archive_permission(): void
    {
        $actor = $this->account('student');
        $request = Request::create('/admin/users/archive-all', 'POST', ['folder_name' => 'Archive']);
        $request->setUserResolver(fn () => $actor);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(AdminController::class)->archiveAllUsers($request);
    }
}
