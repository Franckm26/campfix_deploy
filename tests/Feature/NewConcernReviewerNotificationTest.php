<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Concern;
use App\Models\User;
use App\Notifications\NewConcernSubmittedNotification;
use App\Services\NotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NewConcernReviewerNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('concerns');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('issues')->nullable();
            $table->timestamps();
        });

        Schema::create('concerns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location');
            $table->foreignId('category_id');
            $table->foreignId('user_id');
            $table->string('status')->default('Pending');
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
        });
    }

    public function test_technology_concerns_notify_active_mis_users_instead_of_building_admins(): void
    {
        Notification::fake();

        $reporter = $this->createUser('reporter@example.com', 'student');
        $mis = $this->createUser('mis@example.com', 'mis');
        $archivedMis = $this->createUser('archived-mis@example.com', 'mis', true);
        $buildingAdmin = $this->createUser('building@example.com', 'building_admin');
        $category = Category::create(['name' => 'Technology/Internet']);
        $concern = $this->createConcern($reporter, $category);

        (new NotificationService)->notifyReviewersOfNewConcern($concern);

        Notification::assertSentTo($mis, NewConcernSubmittedNotification::class);
        Notification::assertNotSentTo($archivedMis, NewConcernSubmittedNotification::class);
        Notification::assertNotSentTo($buildingAdmin, NewConcernSubmittedNotification::class);
    }

    public function test_other_concerns_continue_to_notify_building_admins(): void
    {
        Notification::fake();

        $reporter = $this->createUser('reporter-other@example.com', 'faculty');
        $mis = $this->createUser('mis-other@example.com', 'mis');
        $buildingAdmin = $this->createUser('building-other@example.com', 'building_admin');
        $category = Category::create(['name' => 'Maintenance']);
        $concern = $this->createConcern($reporter, $category);

        (new NotificationService)->notifyReviewersOfNewConcern($concern);

        Notification::assertSentTo($buildingAdmin, NewConcernSubmittedNotification::class);
        Notification::assertNotSentTo($mis, NewConcernSubmittedNotification::class);
    }

    private function createUser(string $email, string $role, bool $archived = false): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => $role,
            'is_archived' => $archived,
        ]);
    }

    private function createConcern(User $reporter, Category $category): Concern
    {
        return Concern::create([
            'title' => 'Network connection issue',
            'description' => 'No internet connection',
            'location' => 'Room 301',
            'category_id' => $category->id,
            'user_id' => $reporter->id,
            'status' => 'Pending',
            'is_anonymous' => false,
        ]);
    }
}
