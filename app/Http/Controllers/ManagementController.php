<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\EventRequest;
use App\Models\EventApprovalChain;
use App\Models\EventRequestType;
use App\Models\EventIntendedUser;
use App\Models\EventDepartment;
use App\Models\Facility;
use App\Models\MaintenanceStaff;
use App\Models\User;
use App\Services\DefaultCategoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ManagementController extends Controller
{
    // ─── Access guard ────────────────────────────────────────────────────────
    private function guardBuildingAdmin()
    {
        if (auth()->user()->role !== 'building_admin') {
            abort(403, 'Access denied.');
        }
    }

    // ─── Main management page ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->guardBuildingAdmin();

        $tab = $request->get('tab', 'staff');
        DefaultCategoryService::ensureDefaults();

        // Maintenance staff
        $staffQuery = MaintenanceStaff::query();
        if ($request->filled('staff_search')) {
            $staffQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->staff_search.'%');
            });
        }
        $staff = $staffQuery->orderBy('name')->paginate(10, ['*'], 'staff_page')->withQueryString();

        // Facilities
        $facilityQuery = Facility::query();
        if ($request->filled('facility_search')) {
            $facilityQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->facility_search.'%')
                  ->orWhere('location', 'like', '%'.$request->facility_search.'%');
            });
        }
        if ($request->filled('facility_type')) {
            $facilityQuery->where('type', $request->facility_type);
        }
        $facilities = $facilityQuery->orderBy('name')->paginate(10, ['*'], 'facility_page')->withQueryString();

        // Categories
        $categories = Category::orderBy('name')->paginate(10, ['*'], 'category_page')->withQueryString();

        $eventSetupReady = false;
        $approvalConfigurationReady = false;
        $eventRequestTypes = collect();
        $eventIntendedUsers = collect();
        $eventDepartments = collect();
        $eventApprovalChains = collect();

        try {
            $eventSetupReady = Schema::hasColumns('event_request_types', ['id', 'name', 'requires_department', 'approval_roles', 'is_active'])
                && Schema::hasColumns('event_intended_users', ['id', 'name', 'code', 'approval_roles', 'is_active'])
                && Schema::hasColumns('event_departments', ['id', 'name', 'is_active']);

            if ($eventSetupReady) {
                $eventRequestTypes = EventRequestType::orderBy('name')->get();
                $eventIntendedUsers = EventIntendedUser::orderBy('name')->get();
                $eventDepartments = EventDepartment::orderBy('name')->get();
            }

            $approvalConfigurationReady = $eventSetupReady
                && Schema::hasColumns('event_approval_chains', ['id', 'event_intended_user_id', 'event_request_type_id', 'approval_roles']);

            if ($approvalConfigurationReady) {
                $eventApprovalChains = EventApprovalChain::with(['intendedUser', 'requestType'])->get();
            }
        } catch (Throwable $exception) {
            // A partially applied production migration must not take down the entire
            // management page. The UI will show its migration-required message.
            Log::warning('Event approval configuration could not be loaded.', [
                'exception' => $exception->getMessage(),
            ]);
            $eventSetupReady = false;
            $approvalConfigurationReady = false;
            $eventRequestTypes = collect();
            $eventIntendedUsers = collect();
            $eventDepartments = collect();
            $eventApprovalChains = collect();
        }
        try {
            $events = Schema::hasColumns('event_requests', ['id', 'user_id', 'is_deleted'])
                ? EventRequest::with('user')->where('is_deleted', false)->latest()->paginate(10, ['*'], 'event_page')->withQueryString()
                : new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url(), 'pageName' => 'event_page']);
        } catch (Throwable $exception) {
            Log::warning('Event requests could not be loaded on the management page.', [
                'exception' => $exception->getMessage(),
            ]);
            $events = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url(), 'pageName' => 'event_page']);
        }
        $approvalRoles = [
            'principal_assistant' => 'Principal Assistant', 'program_head' => 'Program Head', 'academic_head' => 'Academic Head',
            'building_admin' => 'Building Admin', 'school_admin' => 'School Administrator',
        ];
        $eventRequestTypeOptions = $eventRequestTypes->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'roles' => $item->approval_roles])->values()->all();
        $eventIntendedUserOptions = $eventIntendedUsers->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'code' => $item->code, 'roles' => $item->approval_roles])->values()->all();
        $eventApprovalChainOptions = $eventApprovalChains->map(fn ($chain) => ['intended_user_id' => $chain->event_intended_user_id, 'request_type_id' => $chain->event_request_type_id, 'roles' => $chain->approval_roles])->values()->all();
        $eventRequestTypeDefaults = $eventRequestTypes->mapWithKeys(fn ($type) => [(string) $type->id => $type->approval_roles])->all();

        return view('admin.management', compact('tab', 'staff', 'facilities', 'categories', 'eventSetupReady', 'approvalConfigurationReady', 'eventRequestTypes', 'eventIntendedUsers', 'eventDepartments', 'eventApprovalChains', 'events', 'approvalRoles', 'eventRequestTypeOptions', 'eventIntendedUserOptions', 'eventApprovalChainOptions', 'eventRequestTypeDefaults'));
    }

    public function storeEventApprovalChain(Request $request)
    {
        $this->guardBuildingAdmin();
        $allowedRoles = ['principal_assistant', 'program_head', 'academic_head', 'building_admin', 'school_admin'];
        $data = $request->validate([
            'event_intended_user_id' => 'required|exists:event_intended_users,id',
            'event_request_type_id' => 'required|exists:event_request_types,id',
            'approval_roles' => 'required|array|min:1',
            'approval_roles.*' => 'required|string|in:'.implode(',', $allowedRoles),
        ]);
        EventApprovalChain::updateOrCreate(
            ['event_intended_user_id' => $data['event_intended_user_id'], 'event_request_type_id' => $data['event_request_type_id']],
            ['approval_roles' => array_values($data['approval_roles'])]
        );
        return back()->with('success', 'Approval chain saved. New event requests will use this route.');
    }

    public function destroyEventApprovalChain(EventApprovalChain $eventApprovalChain)
    {
        $this->guardBuildingAdmin();
        $eventApprovalChain->delete();

        return back()->with('success', 'Approval chain deleted. New event requests will use the request type default until another chain is configured.');
    }

    public function storeEventRequestType(Request $request)
    {
        $this->guardBuildingAdmin();
        $allowedRoles = 'principal_assistant,program_head,academic_head,building_admin,school_admin';
        $data = $request->validate(['name' => 'required|string|max:100|unique:event_request_types,name', 'approval_roles' => 'required|array|min:1', 'approval_roles.*' => 'required|string|in:'.$allowedRoles]);
        $roles = array_values($data['approval_roles']);
        EventRequestType::create(['name' => trim($data['name']), 'requires_department' => in_array('program_head', $roles, true), 'approval_roles' => $roles, 'is_active' => true]);
        return back()->with('success', 'Event request type added.');
    }

    public function updateEventRequestType(Request $request, EventRequestType $eventRequestType)
    {
        $this->guardBuildingAdmin();
        $allowedRoles = 'principal_assistant,program_head,academic_head,building_admin,school_admin';
        $data = $request->validate(['name' => 'required|string|max:100|unique:event_request_types,name,'.$eventRequestType->id, 'approval_roles' => 'required|array|min:1', 'approval_roles.*' => 'required|string|in:'.$allowedRoles, 'is_active' => 'nullable|boolean']);
        $roles = array_values($data['approval_roles']);
        $eventRequestType->update(['name' => trim($data['name']), 'approval_roles' => $roles, 'requires_department' => in_array('program_head', $roles, true), 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Event request type updated.');
    }

    public function storeEventIntendedUser(Request $request)
    {
        $this->guardBuildingAdmin();
        $allowedRoles = 'principal_assistant,program_head,academic_head,building_admin,school_admin';
        $data = $request->validate(['name' => 'required|string|max:100|unique:event_intended_users,name', 'approval_roles' => 'nullable|array', 'approval_roles.*' => 'required|string|in:'.$allowedRoles]);
        $baseCode = Str::slug($data['name'], '_');
        $code = $baseCode;
        $suffix = 2;
        while (EventIntendedUser::where('code', $code)->exists()) $code = $baseCode.'_'.$suffix++;
        EventIntendedUser::create(['name' => trim($data['name']), 'code' => $code, 'approval_roles' => !empty($data['approval_roles']) ? array_values($data['approval_roles']) : null, 'is_active' => true]);
        return back()->with('success', 'Intended user added.');
    }

    public function updateEventIntendedUser(Request $request, EventIntendedUser $eventIntendedUser)
    {
        $this->guardBuildingAdmin();
        $allowedRoles = 'principal_assistant,program_head,academic_head,building_admin,school_admin';
        $data = $request->validate(['name' => 'required|string|max:100|unique:event_intended_users,name,'.$eventIntendedUser->id, 'approval_roles' => 'nullable|array', 'approval_roles.*' => 'required|string|in:'.$allowedRoles]);
        $eventIntendedUser->update(['name' => trim($data['name']), 'approval_roles' => !empty($data['approval_roles']) ? array_values($data['approval_roles']) : null]);
        return back()->with('success', 'Intended user updated.');
    }

    public function storeEventDepartment(Request $request)
    {
        $this->guardBuildingAdmin();
        $data = $request->validate(['name' => 'required|string|max:100|unique:event_departments,name']);
        EventDepartment::create(['name' => trim($data['name']), 'is_active' => true]);
        return back()->with('success', 'Department added.');
    }

    public function toggleEventSetup(Request $request, string $type, int $id)
    {
        return $this->destroyEventSetup($type, $id);
    }

    public function renameEventSetup(Request $request, string $type, int $id)
    {
        $this->guardBuildingAdmin();
        $models = ['request-type' => [EventRequestType::class, 'event_request_types'], 'intended-user' => [EventIntendedUser::class, 'event_intended_users'], 'department' => [EventDepartment::class, 'event_departments']];
        abort_unless(isset($models[$type]), 404);
        [$model, $table] = $models[$type];
        $item = $model::findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:100|unique:'.$table.',name,'.$item->id]);
        $item->update(['name' => trim($data['name'])]);
        return back()->with('success', 'Event setup item updated.');
    }

    public function destroyEventSetup(string $type, int $id)
    {
        $this->guardBuildingAdmin();
        $model = match ($type) { 'request-type' => EventRequestType::class, 'intended-user' => EventIntendedUser::class, 'department' => EventDepartment::class, default => abort(404) };
        $item = $model::findOrFail($id);
        $item->delete();
        return back()->with('success', 'Event setup item deleted.');
    }

    // ─── STAFF ───────────────────────────────────────────────────────────────

    public function storeStaff(Request $request)
    {
        $this->guardBuildingAdmin();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
        ]);

        $staff = MaintenanceStaff::create([
            'name'       => trim($request->first_name.' '.$request->last_name),
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log('maintenance_staff_created', "Created maintenance staff: {$staff->name}", $staff->id, 'maintenance_staff');

        return redirect()->route('admin.management', ['tab' => 'staff'])
            ->with('success', "Maintenance staff '{$staff->name}' added successfully.");
    }

    public function updateStaff(Request $request, $id)
    {
        $this->guardBuildingAdmin();

        $staff = MaintenanceStaff::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
        ]);

        $staff->update([
            'name' => trim($request->first_name.' '.$request->last_name),
        ]);

        ActivityLog::log('maintenance_staff_updated', "Updated maintenance staff: {$staff->name}", $staff->id, 'maintenance_staff');

        return redirect()->route('admin.management', ['tab' => 'staff'])
            ->with('success', "Staff '{$staff->name}' updated successfully.");
    }

    public function destroyStaff($id)
    {
        $this->guardBuildingAdmin();

        $staff = MaintenanceStaff::findOrFail($id);
        $name = $staff->name;

        $staff->delete(); // Soft delete

        ActivityLog::log('maintenance_staff_deleted', "Deleted maintenance staff: {$name}", $staff->id, 'maintenance_staff');

        return redirect()->route('admin.management', ['tab' => 'staff'])
            ->with('success', "Staff '{$name}' removed successfully.");
    }

    // ─── FACILITIES ──────────────────────────────────────────────────────────

    public function storeFacility(Request $request)
    {
        $this->guardBuildingAdmin();

        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:room,court,avr,library,lab,other',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'status'   => 'required|in:available,unavailable,under_maintenance',
        ]);

        $facility = Facility::create([
            'name'        => $request->name,
            'type'        => $request->type,
            'location'    => $request->location,
            'capacity'    => $request->capacity,
            'description' => $request->description,
            'status'      => $request->status,
            'managed_by'  => auth()->id(),
        ]);

        ActivityLog::log('facility_created', "Created facility: {$facility->name}");

        return redirect()->route('admin.management', ['tab' => 'facilities'])
            ->with('success', "Facility '{$facility->name}' added successfully.");
    }

    public function updateFacility(Request $request, $id)
    {
        $this->guardBuildingAdmin();

        $facility = Facility::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:room,court,avr,library,lab,other',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'status'   => 'required|in:available,unavailable,under_maintenance',
        ]);

        $facility->update($request->only('name', 'type', 'location', 'capacity', 'description', 'status'));

        ActivityLog::log('facility_updated', "Updated facility: {$facility->name}");

        return redirect()->route('admin.management', ['tab' => 'facilities'])
            ->with('success', "Facility '{$facility->name}' updated successfully.");
    }

    public function destroyFacility($id)
    {
        $this->guardBuildingAdmin();

        $facility = Facility::findOrFail($id);
        $name = $facility->name;
        $facility->delete();

        ActivityLog::log('facility_deleted', "Deleted facility: {$name}");

        return redirect()->route('admin.management', ['tab' => 'facilities'])
            ->with('success', "Facility '{$name}' deleted successfully.");
    }

    // ─── Quick status toggle for facilities ──────────────────────────────────
    public function updateFacilityStatus(Request $request, $id)
    {
        $this->guardBuildingAdmin();

        $facility = Facility::findOrFail($id);
        $request->validate(['status' => 'required|in:available,unavailable,under_maintenance']);
        $facility->update(['status' => $request->status]);

        return response()->json(['success' => true, 'status' => $facility->status]);
    }

    // ─── CATEGORIES ──────────────────────────────────────────────────────────

    public function storeCategory(Request $request)
    {
        $this->guardBuildingAdmin();

        $request->validate([
            'name'     => 'required|string|max:255|unique:categories,name',
            'issues'   => 'nullable|array',
            'issues.*.name' => 'nullable|string|max:255',
            'issues.*.problem_types' => 'nullable|array',
            'issues.*.problem_types.*' => 'nullable|string|max:255',
        ]);

        $issues = collect($request->input('issues', []))
            ->map(function ($issue) {
                $name = trim((string) ($issue['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                $problemTypes = collect($issue['problem_types'] ?? [])
                    ->map(fn ($problemType) => trim((string) $problemType))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'name' => $name,
                    'problem_types' => $problemTypes,
                ];
            })
            ->filter(fn ($issue) => $issue !== null)
            ->values()
            ->all();

        Category::create(['name' => $request->name, 'issues' => $issues ?: null]);

        return redirect()->route('admin.management', ['tab' => 'categories'])
            ->with('success', "Category '{$request->name}' added successfully.");
    }

    public function updateCategory(Request $request, $id)
    {
        $this->guardBuildingAdmin();

        $category = Category::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255|unique:categories,name,'.$id,
            'issues'   => 'nullable|array',
            'issues.*.name' => 'nullable|string|max:255',
            'issues.*.problem_types' => 'nullable|array',
            'issues.*.problem_types.*' => 'nullable|string|max:255',
        ]);

        $issues = collect($request->input('issues', []))
            ->map(function ($issue) {
                $name = trim((string) ($issue['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                $problemTypes = collect($issue['problem_types'] ?? [])
                    ->map(fn ($problemType) => trim((string) $problemType))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'name' => $name,
                    'problem_types' => $problemTypes,
                ];
            })
            ->filter(fn ($issue) => $issue !== null)
            ->values()
            ->all();

        $category->update(['name' => $request->name, 'issues' => $issues ?: null]);

        return redirect()->route('admin.management', ['tab' => 'categories'])
            ->with('success', "Category updated successfully.");
    }

    public function destroyCategory($id)
    {
        $this->guardBuildingAdmin();

        $category = Category::findOrFail($id);

        if ($category->concerns()->count() > 0) {
            return redirect()->route('admin.management', ['tab' => 'categories'])
                ->with('error', "Cannot delete '{$category->name}' — it has existing concerns.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.management', ['tab' => 'categories'])
            ->with('success', "Category '{$name}' deleted successfully.");
    }
}
