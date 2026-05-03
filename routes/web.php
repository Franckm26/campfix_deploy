<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConcernController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventDiscussionController;
use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

/* HOMEPAGE */
Route::get('/', function () {
    return view('home');
});

/* REQUIRED LOGIN ROUTE FOR AUTH MIDDLEWARE */
Route::get('/login', function () {
    return redirect('/');
})->name('login');

/* AUTH */
Route::middleware(['web', 'throttle:20,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

/* API AUTH - Rate Limited (OWASP A6: Rate Limiting) */
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/api/login', [AuthController::class, 'apiLogin']);
});

/* OTP DELIVERY CHOICE */
Route::get('/otp-choice', function () {
    $userId = session('otp_user');
    if (! $userId) {
        return redirect('/')->with('error', 'Session expired. Please login again.');
    }

    return view('auth.otp-choice');
});

Route::post('/otp-delivery', [AuthController::class, 'sendOtp'])->middleware('throttle:10,1');

Route::get('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend.otp')->middleware('throttle:10,1');

/* DASHBOARD - Role-based redirect */
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

/* PROFILE */
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth')->name('profile.index');
Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth')->name('profile.password');
Route::post('/profile/upload-picture', [ProfileController::class, 'uploadProfilePicture'])->middleware('auth')->name('profile.uploadPicture');
Route::delete('/profile/remove-picture', [ProfileController::class, 'removeProfilePicture'])->middleware('auth')->name('profile.removePicture');

/* SETTINGS */
Route::get('/settings', [SettingsController::class, 'index'])->middleware('auth')->name('settings.index');
Route::put('/settings/notifications', [SettingsController::class, 'updateNotification'])->middleware('auth')->name('settings.notifications');
Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences'])->middleware('auth')->name('settings.preferences');
Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])->middleware('auth')->name('settings.theme');
Route::put('/settings/privacy', [SettingsController::class, 'updatePrivacy'])->middleware('auth')->name('settings.privacy');
Route::put('/settings/security', [SettingsController::class, 'updateSecurity'])->middleware('auth')->name('settings.security');
Route::put('/settings/security-misconfiguration', [SettingsController::class, 'updateSecurityMisconfiguration'])->middleware('auth')->name('settings.security-misconfiguration');

/* FIRST LOGIN PASSWORD CHANGE */
Route::get('/first-login-password', [AuthController::class, 'showFirstLoginPassword'])->middleware('auth')->name('auth.first-login-password');
Route::post('/first-login-password', [AuthController::class, 'updateFirstLoginPassword'])->middleware('auth')->name('auth.first-login-password.update');

/* SECURITY ACCESS VERIFICATION */
Route::post('/verify-access-password', [AuthController::class, 'verifyAccessPassword'])->middleware('auth');

/* LOGOUT */
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

/* NOTIFICATIONS */
Route::middleware('auth')->group(function () {
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

/* USER FEATURES - For students, faculty */
Route::middleware('auth')->group(function () {
    Route::get('/my-concerns', [ConcernController::class, 'myConcerns'])->name('concerns.my');
    Route::get('/user/archive', [ConcernController::class, 'userArchive'])->name('user.archive');
    Route::get('/concerns/create', [ConcernController::class, 'create'])->name('concerns.create');
    Route::post('/submit-concern', [ConcernController::class, 'store'])->name('concerns.store');

    // Maintenance: View assigned concerns - must come before {id} route
    // Maintenance: View assigned reports - must come before {id} route

    Route::get('/concerns/{id}', [ConcernController::class, 'show'])->name('concerns.show');
    Route::get('/concerns/{id}/edit', [ConcernController::class, 'edit'])->name('concerns.edit');
    Route::put('/concerns/{id}', [ConcernController::class, 'update'])->name('concerns.update');
    Route::delete('/concerns/{id}', [ConcernController::class, 'destroy'])->name('concerns.destroy');
    Route::post('/concerns/{id}/archive', [ConcernController::class, 'archive'])->name('concerns.archive');
    Route::post('/concerns/{id}/restore', [ConcernController::class, 'restore'])->name('concerns.restore');
    Route::post('/concerns/{id}/soft-delete', [ConcernController::class, 'softDelete'])->name('concerns.softDelete');
    Route::post('/concerns/{id}/restore-deleted', [ConcernController::class, 'restoreDeleted'])->name('concerns.restore-deleted');
    Route::delete('/concerns/{id}/permanent-delete', [ConcernController::class, 'permanentDelete'])->name('concerns.permanentDelete');
    Route::post('/concerns/{id}/send-follow-up', [ConcernController::class, 'sendFollowUp'])->name('concerns.sendFollowUp');

    // Assign concern to maintenance staff
    Route::post('/concerns/{id}/assign', [ConcernController::class, 'assign'])->name('concerns.assign');

    // Batch operations
    Route::post('/concerns/batch-archive', [ConcernController::class, 'batchArchive'])->name('concerns.batchArchive');
    Route::post('/concerns/batch-soft-delete', [ConcernController::class, 'batchSoftDelete'])->name('concerns.batchSoftDelete');
    Route::post('/concerns/batch-restore', [ConcernController::class, 'batchRestore'])->name('concerns.batchRestore');
    Route::post('/concerns/batch-restore-deleted', [ConcernController::class, 'batchRestoreDeleted'])->name('concerns.batchRestoreDeleted');
    Route::post('/concerns/batch-permanent-delete', [ConcernController::class, 'batchPermanentDelete'])->name('concerns.batchPermanentDelete');

    // Get maintenance users for assignment
    Route::get('/api/maintenance-users', [ConcernController::class, 'getMaintenanceUsers']);

    // API routes for modal data
    Route::get('/api/concerns/{id}', [ConcernController::class, 'apiShow'])->name('concerns.api.show');
    Route::get('/api/concerns/{id}/edit-data', [ConcernController::class, 'apiEdit'])->name('concerns.api.edit');
    Route::get('/api/reports/{report}', [ReportController::class, 'apiShow'])->name('reports.api.show');
    Route::get('/api/reports/{report}/edit-data', [ReportController::class, 'apiEdit'])->name('reports.api.edit');

    // Auto-delete preferences
    Route::post('/save-auto-delete-preference', [AdminController::class, 'saveAutoDeletePreference'])->name('saveAutoDeletePreference');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/deleted', [ReportController::class, 'deleted'])->name('reports.deleted');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('/reports/{report}/archive', [ReportController::class, 'archive'])->name('reports.archive');
    Route::post('/reports/{report}/restore', [ReportController::class, 'restore'])->name('reports.restore');
});

/* MAINTENANCE - Update status routes */
Route::middleware('auth')->group(function () {
    // Update status for assigned concerns
    Route::post('/update-status/{id}', [AdminController::class, 'updateStatus'])->name('admin.updateStatus');

    // Update status for assigned reports
    Route::post('/update-report-status/{id}', [AdminController::class, 'updateReportStatus'])->name('admin.updateReportStatus');
    
    // Update report status (new route for progress tracking)
    Route::post('/reports/{id}/update-status', [ReportController::class, 'updateStatus'])->name('reports.updateStatus');

    Route::post('/resolution-notes/{id}', [AdminController::class, 'addResolutionNotes'])->name('admin.resolution');
    Route::post('/report-resolution-notes/{id}', [AdminController::class, 'addReportResolutionNotes'])->name('admin.report-resolution');

    // MIS: Acknowledge a concern
    Route::post('/concerns/{id}/mis-acknowledge', [ConcernController::class, 'misAcknowledge'])->name('concerns.mis-acknowledge');

    // API: Get a single concern details
    Route::get('/api/concerns/{id}', [ConcernController::class, 'apiShow']);

    // API: Get maintenance users for assignment
    Route::get('/admin/maintenance-users', [AdminController::class, 'getMaintenanceUsers']);
});

/* FACULTY - Event Requests */
Route::middleware('auth')->group(function () {
    // Event requests
    Route::get('/events/create', [EventRequestController::class, 'create'])->name('events.create');
    Route::post('/events', [EventRequestController::class, 'store'])->name('events.store');
    Route::get('/events/{id}', [EventRequestController::class, 'show'])->name('events.show');
    Route::get('/my-events', [EventRequestController::class, 'myRequests'])->name('events.my');
    Route::post('/events/{id}/cancel', [EventRequestController::class, 'cancel'])->name('events.cancel');
    Route::post('/events/{id}/archive', [EventRequestController::class, 'archive'])->name('events.archive');
    Route::post('/events/{id}/restore', [EventRequestController::class, 'restore'])->name('events.restore');
    Route::post('/events/{id}/delete', [EventRequestController::class, 'delete'])->name('events.delete');

    // Event Discussions - Chat/Forum
    Route::get('/events/{eventRequest}/discussions', [EventDiscussionController::class, 'index']);
    Route::post('/events/{eventRequest}/discussions', [EventDiscussionController::class, 'store']);
    Route::delete('/discussions/{discussion}', [EventDiscussionController::class, 'destroy']);

    // API: Check room availability
    Route::post('/api/check-room-availability', [EventRequestController::class, 'checkRoomAvailability']);

    // API: Check court availability
    Route::post('/api/check-court-availability', [EventRequestController::class, 'checkCourtAvailability']);

    // API: Check AVR availability
    Route::post('/api/check-avr-availability', [EventRequestController::class, 'checkAvrAvailability']);
});

/* APPROVAL - For Principal/Admin */
Route::middleware('auth')->group(function () {
    Route::post('/events/{id}/approve', [EventRequestController::class, 'approve'])->name('events.approve');
    Route::post('/events/{id}/reject', [EventRequestController::class, 'reject'])->name('events.reject');
    Route::get('/events-calendar', [EventRequestController::class, 'calendar'])->name('events.calendar');
    Route::get('/events-calendar/events', [EventRequestController::class, 'calendarEvents'])->name('events.calendar.events');
    Route::post('/events-import', [EventRequestController::class, 'import'])->name('events.import');
    Route::get('/events/{id}/pdf', [EventRequestController::class, 'generatePdf'])->name('events.pdf');
});

/* BUILDING ADMIN - Management Module */
Route::middleware('auth')->group(function () {
    Route::get('/admin/management', [\App\Http\Controllers\ManagementController::class, 'index'])->name('admin.management');

    // Maintenance staff
    Route::post('/admin/management/staff', [\App\Http\Controllers\ManagementController::class, 'storeStaff'])->name('admin.management.staff.store');
    Route::put('/admin/management/staff/{id}', [\App\Http\Controllers\ManagementController::class, 'updateStaff'])->name('admin.management.staff.update');
    Route::delete('/admin/management/staff/{id}', [\App\Http\Controllers\ManagementController::class, 'destroyStaff'])->name('admin.management.staff.destroy');

    // Facilities
    Route::post('/admin/management/facilities', [\App\Http\Controllers\ManagementController::class, 'storeFacility'])->name('admin.management.facilities.store');
    Route::put('/admin/management/facilities/{id}', [\App\Http\Controllers\ManagementController::class, 'updateFacility'])->name('admin.management.facilities.update');
    Route::delete('/admin/management/facilities/{id}', [\App\Http\Controllers\ManagementController::class, 'destroyFacility'])->name('admin.management.facilities.destroy');
    Route::patch('/admin/management/facilities/{id}/status', [\App\Http\Controllers\ManagementController::class, 'updateFacilityStatus'])->name('admin.management.facilities.status');

    // Categories (in management)
    Route::post('/admin/management/categories', [\App\Http\Controllers\ManagementController::class, 'storeCategory'])->name('admin.management.categories.store');
    Route::put('/admin/management/categories/{id}', [\App\Http\Controllers\ManagementController::class, 'updateCategory'])->name('admin.management.categories.update');
    Route::delete('/admin/management/categories/{id}', [\App\Http\Controllers\ManagementController::class, 'destroyCategory'])->name('admin.management.categories.destroy');
});

/* ADMIN PANEL - ADMIN ONLY */
Route::middleware(['auth', 'admin'])->group(function () {
    // Main admin dashboard
    Route::get('/admin', [AdminController::class, 'index']);

    // MIS Task Module
    Route::get('/admin/mis-tasks', [AdminController::class, 'misTasks'])->name('admin.mis-tasks');

    // Archive management
    Route::get('/admin/archive', [AdminController::class, 'archive'])->name('admin.archive');
    Route::post('/admin/archive/restore', [AdminController::class, 'restoreArchivedItem'])->name('admin.archive.restore');

    // Category management
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/admin/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Reports
    Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/admin/export', [AdminController::class, 'exportCsv'])->name('admin.export');
    Route::get('/admin/export-pdf', [AdminController::class, 'exportPdf'])->name('admin.export.pdf');

    // User management
    Route::post('/admin/reauth', [AdminController::class, 'reauth'])->name('admin.reauth');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/admin/users/archive-all', [AdminController::class, 'archiveAllUsers'])->name('admin.users.archiveAll');
    Route::delete('/admin/users/delete-all', [AdminController::class, 'deleteAllUsers'])->name('admin.users.deleteAll');
    Route::post('/admin/users/unlock/{uuid}', [AdminController::class, 'unlockUser'])->name('admin.users.unlock');
    Route::post('/admin/users/archive-selected', [AdminController::class, 'archiveSelectedUsers'])->name('admin.users.archiveSelected');
    Route::post('/admin/users/delete-all-archived', [AdminController::class, 'deleteAllArchived'])->name('admin.users.deleteAllArchived');
    Route::post('/admin/users/restore-selected', [AdminController::class, 'restoreSelectedUsers'])->name('admin.users.restoreSelected');
    Route::post('/admin/users/restore-all-folder/{folder_id}', [AdminController::class, 'restoreAllFolderUsers'])->name('admin.users.restoreAllFolder');
    Route::post('/admin/users/import', [AdminController::class, 'importUsers'])->name('admin.users.import');
    Route::get('/admin/users/archive-folders/{id}', [AdminController::class, 'archiveFolderUsers'])->name('admin.archiveFolderUsers');
    Route::delete('/admin/users/archive-folders/{id}', [AdminController::class, 'deleteArchiveFolder'])->name('admin.archiveFolder.delete');
    Route::get('/admin/users/{uuid}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{uuid}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{uuid}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::post('/admin/users/{uuid}/archive', [AdminController::class, 'archiveUser'])->name('admin.users.archive');
    Route::post('/admin/users/{uuid}/restore', [AdminController::class, 'restoreUser'])->name('admin.users.restore');

    // Deleted users management
    Route::get('/admin/deleted-users', [AdminController::class, 'deletedUsers'])->name('admin.deletedUsers');
    Route::post('/admin/deleted-users/restore-all', [AdminController::class, 'restoreAllDeletedUsers'])->name('admin.deletedUsers.restoreAll');
    Route::post('/admin/deleted-users/restore-selected', [AdminController::class, 'restoreSelectedDeletedUsers'])->name('admin.deletedUsers.restoreSelected');
    Route::post('/admin/deleted-users/{id}/restore', [AdminController::class, 'restoreDeletedUser'])->name('admin.deletedUsers.restore');
    Route::delete('/admin/deleted-users', [AdminController::class, 'permanentDeleteAllDeleted'])->name('admin.deletedUsers.permanentDeleteAll');
    Route::delete('/admin/deleted-users/{id}', [AdminController::class, 'permanentDeleteUser'])->name('admin.deletedUsers.permanentDelete');

    // Deleted reports management
    Route::get('/admin/deleted-reports', [AdminController::class, 'deletedReports'])->name('admin.deletedReports');
    Route::post('/admin/deleted-reports/auto-delete', [AdminController::class, 'autoDeleteOldReports'])->name('admin.deletedReports.autoDelete');
    Route::post('/admin/deleted-reports/{id}/restore', [AdminController::class, 'restoreDeletedReport'])->name('admin.deletedReports.restore');
    Route::post('/admin/deleted-reports/restore-selected', [AdminController::class, 'restoreSelectedDeletedReports'])->name('admin.deletedReports.restoreSelected');
    Route::delete('/admin/deleted-reports/{id}', [AdminController::class, 'permanentDeleteReport'])->name('admin.deletedReports.permanentDelete');
    Route::delete('/admin/deleted-reports', [AdminController::class, 'permanentDeleteAllReports'])->name('admin.deletedReports.permanentDeleteAll');

    // Archive concerns (admin can archive any concern)
    Route::post('/admin/concerns/{id}/archive', [AdminController::class, 'archiveConcern'])->name('admin.concerns.archive');

    // Soft delete concerns (move to deleted folder)
    Route::post('/admin/concerns/{id}/soft-delete', [AdminController::class, 'softDeleteConcern'])->name('admin.concerns.softDelete');

    // Soft delete archived reports, events, facilities (admin)
    Route::post('/admin/reports/{id}/soft-delete', [AdminController::class, 'softDeleteArchivedReport'])->name('admin.reports.softDelete');
    Route::post('/admin/events/{id}/soft-delete', [AdminController::class, 'softDeleteArchivedEvent'])->name('admin.events.softDelete');
    Route::post('/admin/facilities/{id}/soft-delete', [AdminController::class, 'softDeleteArchivedFacility'])->name('admin.facilities.softDelete');

    // MIS Task specific archive and delete routes
    Route::post('/admin/mis-tasks/{id}/archive', [AdminController::class, 'archiveMisTaskConcern'])->name('admin.mis-tasks.archive');
    Route::post('/admin/mis-tasks/{id}/delete', [AdminController::class, 'deleteMisTaskConcern'])->name('admin.mis-tasks.delete');
    Route::post('/admin/mis-tasks/{id}/restore', [AdminController::class, 'restoreMisTaskConcern'])->name('admin.mis-tasks.restore');
    Route::post('/admin/mis-tasks/{id}/restore-deleted', [AdminController::class, 'restoreDeletedMisTaskConcern'])->name('admin.mis-tasks.restore-deleted');

    // Deleted events management
    Route::get('/admin/deleted-events', [AdminController::class, 'deletedEvents'])->name('admin.deletedEvents');
    Route::get('/admin/deleted-events/{id}', [AdminController::class, 'viewDeletedEvent'])->name('admin.deletedEvents.view');
    Route::post('/admin/deleted-events/{id}/restore', [AdminController::class, 'restoreDeletedEvent'])->name('admin.deletedEvents.restore');
    Route::post('/admin/deleted-events/restore-selected', [AdminController::class, 'restoreSelectedDeletedEvents'])->name('admin.deletedEvents.restoreSelected');
    Route::delete('/admin/deleted-events/{id}', [AdminController::class, 'permanentDeleteEvent'])->name('admin.deletedEvents.permanentDelete');
    Route::delete('/admin/deleted-events', [AdminController::class, 'permanentDeleteAllEvents'])->name('admin.deletedEvents.permanentDeleteAll');

    // Activity logs
    Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::post('/admin/logs/archive-all', [AdminController::class, 'archiveLogsBulk'])->name('admin.logs.archive.bulk');
    Route::post('/admin/logs/{log}/restore', [AdminController::class, 'restoreLog'])->name('admin.logs.restore');
    Route::delete('/admin/logs/{log}', [AdminController::class, 'deleteLog'])->name('admin.logs.delete');
    Route::get('/admin/logs/folders/{id}', [AdminController::class, 'logArchiveFolder'])->name('admin.logs.folder');
    Route::post('/admin/logs/folders/{id}/restore', [AdminController::class, 'restoreLogArchiveFolder'])->name('admin.logs.folder.restore');
    Route::delete('/admin/logs/folders/{id}', [AdminController::class, 'deleteLogArchiveFolder'])->name('admin.logs.folder.delete');

    // Archive Folders for Items (concerns, reports, facilities)
    Route::get('/admin/archive-folders', [AdminController::class, 'archiveFolders'])->name('admin.archiveFolders');
    Route::post('/admin/archive-folders/create', [AdminController::class, 'createArchiveFolder'])->name('admin.archiveFolders.create');
    Route::get('/admin/archive-folders/{id}/edit', [AdminController::class, 'editArchiveFolder'])->name('admin.archiveFolders.edit');
    Route::put('/admin/archive-folders/{id}', [AdminController::class, 'updateArchiveFolder'])->name('admin.archiveFolders.update');
    Route::get('/admin/archive-folders/{id}/items', [AdminController::class, 'archiveFolderItems'])->name('admin.archiveFolderItems');
    Route::post('/admin/archive/to-folder', [AdminController::class, 'moveToArchiveFolder'])->name('admin.archive.toFolder');
    Route::post('/admin/archive-folders/{id}/restore-all', [AdminController::class, 'restoreAllFromFolder'])->name('admin.archiveFolder.restoreAll');
    Route::post('/admin/archive-folders/{id}/restore-selected', [AdminController::class, 'restoreSelectedFromFolder'])->name('admin.archiveFolder.restoreSelected');

    // Events management
    Route::get('/admin/events', [EventRequestController::class, 'adminIndex'])->name('admin.events');

    // Analytics
    Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
    Route::get('/admin/analytics/export', [AdminController::class, 'exportAnalytics'])->name('admin.analytics.export');
    Route::post('/admin/concern/{id}/cost', [AdminController::class, 'updateCost'])->name('admin.concern.updateCost');

    // Building Admin: Assign concerns to maintenance
    Route::post('/admin/concern/{id}/assign', [AdminController::class, 'assignConcern'])->name('admin.concern.assign');

    // Building Admin: Assign reports to maintenance
    Route::post('/admin/report/{id}/assign', [AdminController::class, 'assignReport'])->name('admin.report.assign');

    // Building Admin: Set priority after assignment
    Route::post('/admin/report/{id}/priority', [AdminController::class, 'setReportPriority'])->name('admin.report.priority');
    Route::post('/admin/concern/{id}/priority', [AdminController::class, 'setConcernPriority'])->name('admin.concern.priority');

    // Building Admin: Get maintenance users list
    Route::get('/admin/maintenance-users', [AdminController::class, 'getMaintenanceUsers'])->name('admin.maintenance.users');

});

/* OTP VERIFICATION */
Route::get('/verify-otp', function () {
    return view('auth.verify-otp');
});

Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

/* SUPERADMIN PANEL - SUPERADMIN ONLY (HIDDEN FROM ALL OTHER USERS) */
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\SuperadminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('/users', [\App\Http\Controllers\SuperadminController::class, 'users'])->name('users');
    Route::get('/users/create', [\App\Http\Controllers\SuperadminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\SuperadminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{uuid}/edit', [\App\Http\Controllers\SuperadminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{uuid}', [\App\Http\Controllers\SuperadminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{uuid}', [\App\Http\Controllers\SuperadminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{uuid}/restore', [\App\Http\Controllers\SuperadminController::class, 'restoreUser'])->name('users.restore');
    Route::post('/users/{uuid}/unlock', [\App\Http\Controllers\SuperadminController::class, 'unlockUser'])->name('users.unlock');
    
    // System Overview
    Route::get('/concerns', [\App\Http\Controllers\SuperadminController::class, 'concerns'])->name('concerns');
    Route::delete('/concerns/{id}/force-delete', [\App\Http\Controllers\SuperadminController::class, 'forceDeleteConcern'])->name('concerns.force-delete');
    
    Route::get('/reports', [\App\Http\Controllers\SuperadminController::class, 'reports'])->name('reports');
    Route::delete('/reports/{id}/force-delete', [\App\Http\Controllers\SuperadminController::class, 'forceDeleteReport'])->name('reports.force-delete');
    
    Route::get('/events', [\App\Http\Controllers\SuperadminController::class, 'events'])->name('events');
    Route::delete('/events/{id}/force-delete', [\App\Http\Controllers\SuperadminController::class, 'forceDeleteEvent'])->name('events.force-delete');
    
    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\SuperadminController::class, 'activityLogs'])->name('activity-logs');
    Route::delete('/activity-logs/{id}', [\App\Http\Controllers\SuperadminController::class, 'deleteActivityLog'])->name('activity-logs.delete');
    Route::delete('/activity-logs', [\App\Http\Controllers\SuperadminController::class, 'clearAllActivityLogs'])->name('activity-logs.clear');
    
    // Superadmin Logs (Hidden from regular admins)
    Route::get('/superadmin-logs', [\App\Http\Controllers\SuperadminController::class, 'superadminLogs'])->name('superadmin-logs');
    
    // Categories
    Route::get('/categories', [\App\Http\Controllers\SuperadminController::class, 'categories'])->name('categories');
    Route::post('/categories', [\App\Http\Controllers\SuperadminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}', [\App\Http\Controllers\SuperadminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [\App\Http\Controllers\SuperadminController::class, 'deleteCategory'])->name('categories.delete');
    
    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\SuperadminController::class, 'analytics'])->name('analytics');
    
    // Settings
    Route::get('/settings', [\App\Http\Controllers\SuperadminController::class, 'settings'])->name('settings');
    Route::put('/settings', [\App\Http\Controllers\SuperadminController::class, 'updateSettings'])->name('settings.update');
});

/* EVENTS */
Route::get('/events', function () {
    $events = Event::all()->map(function ($event) {
        return [
            'title' => $event->title,
            'start' => $event->date,
        ];
    });

    // Also get approved event requests
    $eventRequests = \App\Models\EventRequest::where('status', 'Approved')->get()->map(function ($er) {
        return [
            'title' => $er->title,
            'start' => $er->event_date,
        ];
    });

    return $events->concat($eventRequests)->values();
});
