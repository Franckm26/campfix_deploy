# Role Permissions Reference

This document shows the default permissions for each role in the system.

## Permission Modules Available

1. **concerns** - Concerns Management
2. **reports** - Reports Management
3. **events** - Event Requests
4. **users** - User Management (view)
5. **users_create** - Create Users
6. **users_archive** - Archive Users
7. **users_lock** - Lock Users
8. **users_unlock** - Unlock Users
9. **users_edit** - Edit Users
10. **users_delete** - Delete Users
11. **module_access** - Module Access Control
12. **categories** - Categories Management
13. **logs** - Audit Logs
14. **analytics** - Analytics Dashboard
15. **mis_tasks** - MIS Tasks
16. **settings** - User Settings

---

## Role Permissions Matrix

### 1. MIS (Management Information System)
**Purpose**: System administrators with full user management capabilities

**Permissions**:
- ✅ Concerns
- ✅ Events
- ✅ Users (full CRUD: view, create, archive, lock, unlock, edit, delete)
- ✅ Module Access Control
- ✅ Categories
- ✅ Audit Logs
- ✅ MIS Tasks
- ✅ Settings
- ❌ Reports
- ❌ Analytics

**Dashboard**: Admin Dashboard

---

### 2. School Administrator
**Purpose**: School-level administrators with oversight capabilities

**Permissions**:
- ✅ Concerns
- ✅ Reports
- ✅ Events
- ✅ Analytics
- ✅ Settings
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Admin Dashboard

---

### 3. Building Administrator
**Purpose**: Building-level administrators with facility oversight

**Permissions**:
- ✅ Concerns
- ✅ Reports
- ✅ Events
- ✅ Analytics
- ✅ Settings
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Admin Dashboard

---

### 4. Academic Head
**Purpose**: Academic department heads managing events

**Permissions**:
- ✅ Events
- ✅ Settings
- ❌ Concerns
- ❌ Reports
- ❌ Analytics
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Faculty Dashboard

---

### 5. Program Head
**Purpose**: Program-level heads managing events

**Permissions**:
- ✅ Events
- ✅ Settings
- ❌ Concerns
- ❌ Reports
- ❌ Analytics
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Faculty Dashboard

---

### 6. Principal Assistant
**Purpose**: Assistant to principal managing events

**Permissions**:
- ✅ Events
- ✅ Settings
- ❌ Concerns
- ❌ Reports
- ❌ Analytics
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Faculty Dashboard

---

### 7. Maintenance
**Purpose**: Maintenance staff handling facility reports and concerns

**Permissions**:
- ✅ Reports
- ✅ Concerns
- ✅ Settings
- ❌ Events
- ❌ Analytics
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Faculty Dashboard

---

### 8. Faculty
**Purpose**: Teaching staff managing events and concerns

**Permissions**:
- ✅ Events
- ✅ Concerns
- ✅ Settings
- ❌ Reports
- ❌ Analytics
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Faculty Dashboard

---

### 9. Student
**Purpose**: Students submitting concerns

**Permissions**:
- ✅ Concerns
- ✅ Settings
- ❌ Reports
- ❌ Events
- ❌ Analytics
- ❌ Users
- ❌ Module Access
- ❌ Categories
- ❌ Logs
- ❌ MIS Tasks

**Dashboard**: Student Dashboard

---

## Import Behavior

When importing users via CSV:

1. **Role Detection**: 
   - For Staff imports, role is auto-detected from STAFF column (column 7)
   - For Student/Faculty imports, role is set based on import type selection

2. **Permission Assignment**:
   - Permissions are automatically assigned using `User::defaultPermissions($role)`
   - No manual permission selection required
   - Permissions match the matrix above

3. **Staff Role Mapping**:
   - MIS → `mis` role
   - School Administrator → `school_admin` role
   - Building Administrator → `building_admin` role
   - Academic Head → `academic_head` role
   - Program Head → `program_head` role
   - Principal Assistant → `principal_assistant` role
   - Maintenance/Laboratory Custodian → `maintenance` role
   - All other positions → `faculty` role

---

## Notes

- **Superadmin** has access to all modules (not shown in matrix)
- Permissions can be manually customized per user after creation
- Import process uses these defaults automatically
- JavaScript `importRoleDefaults` mirrors PHP `User::defaultPermissions()`

---

**Last Updated**: May 10, 2026
**Files**:
- `app/Models/User.php` - `defaultPermissions()` method
- `resources/views/admin/users.blade.php` - `importRoleDefaults` object
- `app/Http/Controllers/AdminController.php` - Import logic (line 3474)
