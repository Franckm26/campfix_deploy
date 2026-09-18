<?php

namespace Tests\Feature;

use App\Http\Controllers\ManagementController;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ManagementCategoryScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('facilities');
        Schema::dropIfExists('maintenance_staff');
        Schema::dropIfExists('event_departments');
        Schema::dropIfExists('categories');

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('issues')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('available');
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('event_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Category::create(['name' => 'Maintenance']);
        Category::create(['name' => 'Cleaning']);
        Category::create(['name' => 'Technology/Internet']);
        Category::create(['name' => 'Unassigned Category']);
    }

    public function test_mis_management_hides_staff_and_shows_only_technology_internet(): void
    {
        $this->actingAs($this->roleUser('mis'));

        $view = app(ManagementController::class)->index(Request::create('/mis/management', 'GET', ['tab' => 'staff']));

        $this->assertSame('categories', $view->getData()['tab']);
        $this->assertTrue($view->getData()['isMisManagement']);
        $this->assertSame(['Technology/Internet'], $view->getData()['categories']->pluck('name')->all());

        $facilitiesView = app(ManagementController::class)->index(Request::create('/mis/management', 'GET', ['tab' => 'facilities']));
        $eventsView = app(ManagementController::class)->index(Request::create('/mis/management', 'GET', ['tab' => 'events']));

        $this->assertSame('facilities', $facilitiesView->getData()['tab']);
        $this->assertSame('events', $eventsView->getData()['tab']);
    }

    public function test_building_administrator_management_keeps_only_cleaning_and_maintenance_categories(): void
    {
        $this->actingAs($this->roleUser('building_admin'));

        $view = app(ManagementController::class)->index(Request::create('/building-admin/management', 'GET', ['tab' => 'categories']));

        $this->assertFalse($view->getData()['isMisManagement']);
        $this->assertSame(['Cleaning', 'Maintenance'], $view->getData()['categories']->pluck('name')->all());
    }

    public function test_mis_cannot_modify_a_building_administrator_category_with_a_crafted_request(): void
    {
        $this->actingAs($this->roleUser('mis'));
        $maintenance = Category::where('name', 'Maintenance')->firstOrFail();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('You cannot manage this category.');

        app(ManagementController::class)->updateCategory(
            Request::create('/admin/management/categories/'.$maintenance->id, 'PUT', [
                'name' => 'Maintenance',
                'issues' => [],
            ]),
            $maintenance->id
        );
    }

    public function test_mis_can_manage_facilities_and_event_setup(): void
    {
        $this->actingAs($this->roleUser('mis'));
        $facility = \App\Models\Facility::create([
            'name' => 'Computer Laboratory',
            'type' => 'lab',
            'status' => 'available',
        ]);

        $statusResponse = app(ManagementController::class)->updateFacilityStatus(
            Request::create('/admin/management/facilities/'.$facility->id.'/status', 'PATCH', ['status' => 'under_maintenance']),
            $facility->id
        );
        $this->assertSame('under_maintenance', $statusResponse->getData(true)['status']);
        $this->assertSame('under_maintenance', $facility->fresh()->status);

        app(ManagementController::class)->storeEventDepartment(
            Request::create('/admin/management/event-setup/departments', 'POST', ['name' => 'Information Technology'])
        );
        $this->assertDatabaseHas('event_departments', ['name' => 'Information Technology', 'is_active' => true]);
    }

    private function roleUser(string $role): User
    {
        $user = new User;
        $user->forceFill([
            'id' => $role === 'mis' ? 101 : 102,
            'name' => str($role)->replace('_', ' ')->title()->toString(),
            'email' => $role.'@example.com',
            'role' => $role,
            'is_superadmin' => false,
        ]);

        return $user;
    }
}
