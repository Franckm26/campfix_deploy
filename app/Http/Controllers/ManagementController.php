<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Facility;
use App\Models\MaintenanceStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        // Maintenance staff
        $staffQuery = MaintenanceStaff::query();
        if ($request->filled('staff_search')) {
            $staffQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->staff_search.'%');
            });
        }
        $staff = $staffQuery->orderBy('name')->get();

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
        if ($request->filled('facility_status')) {
            $facilityQuery->where('status', $request->facility_status);
        }
        $facilities = $facilityQuery->orderBy('name')->get();

        // Categories
        $categories = Category::orderBy('name')->get();

        return view('admin.management', compact('tab', 'staff', 'facilities', 'categories'));
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
            'issues.*' => 'string|max:255',
        ]);

        $issues = array_values(array_filter(array_map('trim', $request->input('issues', []))));

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
            'issues.*' => 'string|max:255',
        ]);

        $issues = array_values(array_filter(array_map('trim', $request->input('issues', []))));

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
