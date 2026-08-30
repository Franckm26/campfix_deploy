<?php

namespace Tests\Feature;

use App\Http\Controllers\RoleAnalyticsController;
use App\Models\Category;
use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleScopedAnalyticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('reports');
        Schema::dropIfExists('concerns');
        Schema::dropIfExists('event_requests');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('department')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_superadmin')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();
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
            $table->foreignId('user_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('Pending');
            $table->string('priority')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->default('Pending');
            $table->boolean('is_deleted')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('event_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('request_type')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('intended_user')->nullable();
            $table->string('education_level')->nullable();
            $table->string('department')->nullable();
            $table->string('status')->default('Pending');
            $table->string('priority')->nullable();
            $table->unsignedInteger('approval_level')->default(1);
            $table->json('approval_route')->nullable();
            $table->json('approval_history')->nullable();
            $table->unsignedBigInteger('approved_by_level_1')->nullable();
            $table->unsignedBigInteger('approved_by_level_2')->nullable();
            $table->unsignedBigInteger('approved_by_level_3')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('event_date')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    public function test_mis_analytics_uses_only_the_technology_internet_task_queue(): void
    {
        $mis = $this->user('MIS User', 'mis');
        $technology = Category::create(['name' => 'Technology/Internet']);
        $facilities = Category::create(['name' => 'Facilities']);

        Concern::create(['user_id' => $mis->id, 'category_id' => $technology->id, 'title' => 'No internet', 'status' => 'Pending', 'priority' => 'urgent']);
        Concern::create(['user_id' => $mis->id, 'category_id' => $facilities->id, 'title' => 'Broken chair', 'status' => 'Pending']);

        $view = app(RoleAnalyticsController::class)->index($this->requestFor($mis));

        $this->assertSame('mis', $view->getData()['mode']);
        $this->assertSame(['No internet'], $view->getData()['recentItems']->pluck('title')->all());
        $this->assertSame(1, $view->getData()['metrics'][2]['value']);
        $this->assertSame('No internet', $view->getData()['operations']->first()['name']);
        $this->assertSame(1, $view->getData()['operations']->first()['stats']['urgent']);
        $this->assertSame(
            ['Unassigned MIS work', 'High-priority tasks'],
            $view->getData()['decisionAlerts']->pluck('title')->all()
        );
    }

    public function test_building_admin_report_scope_excludes_technology_internet(): void
    {
        $buildingAdmin = $this->user('Building Admin', 'building_admin');
        $technology = Category::create(['name' => 'Technology/Internet']);
        $facilities = Category::create(['name' => 'Facilities']);

        Report::create(['category_id' => $technology->id, 'title' => 'Router issue']);
        Report::create(['category_id' => $facilities->id, 'title' => 'Broken desk']);

        $mis = $this->user('Second MIS User', 'mis');
        $this->assertSame(
            ['Router issue'],
            Report::query()->forOperationalRole($mis)->pluck('title')->all()
        );
        $this->assertSame(
            ['Broken desk'],
            Report::query()->forOperationalRole($buildingAdmin)->pluck('title')->all()
        );
    }

    public function test_program_head_analytics_is_limited_to_its_department_and_approval_route(): void
    {
        $programHead = $this->user('ICT Head', 'program_head', 'ICT');
        $requester = $this->user('Requester', 'faculty');

        EventRequest::create($this->eventData($requester, 'ICT'));
        EventRequest::create($this->eventData($requester, 'Business Management'));

        $view = app(RoleAnalyticsController::class)->index($this->requestFor($programHead));

        $this->assertSame('approval', $view->getData()['mode']);
        $this->assertCount(1, $view->getData()['recentItems']);
        $this->assertSame('ICT', $view->getData()['recentItems']->first()->department);
        $this->assertSame(1, $view->getData()['metrics'][1]['value']);
        $this->assertSame('Academic', $view->getData()['operations']->first()['name']);
        $this->assertSame(1, $view->getData()['operations']->first()['stats']['open']);
        $this->assertSame('Requests awaiting your decision', $view->getData()['decisionAlerts']->first()['title']);
    }

    private function user(string $name, string $role, ?string $department = null): User
    {
        return User::create([
            'name' => $name,
            'email' => str($name)->slug().'-'.$role.'@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'department' => $department,
            'description' => 'Department event request',
            'location' => 'Open Lobby',
        ]);
    }

    private function requestFor(User $user): Request
    {
        $request = Request::create('/role-analytics', 'GET');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function eventData(User $requester, string $department): array
    {
        return [
            'user_id' => $requester->id,
            'request_type' => 'Academic',
            'intended_user' => 'tertiary',
            'education_level' => 'tertiary',
            'department' => $department,
            'status' => 'Pending',
            'approval_level' => 1,
            'approval_route' => ['program_head', 'academic_head', 'building_admin', 'school_admin'],
            'approval_history' => [],
            'event_date' => now()->addWeek()->toDateString(),
        ];
    }
}
