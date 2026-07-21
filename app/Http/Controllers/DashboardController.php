<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\Report;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        EventRequestController::rejectExpiredPendingRequests();

        // Redirect based on role
        if ($user->is_superadmin || $user->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->role === 'mis') {
            return redirect('/admin');
        }

        // Building Administrator - original dashboard with all cards
        if ($user->role === 'building_admin') {
            $hasProgramHead  = \App\Models\User::where('role', 'program_head')->exists();
            $hasAcademicHead = \App\Models\User::where('role', 'academic_head')->exists();

            // Determine which approval levels building admin can act on
            if (!$hasProgramHead && !$hasAcademicHead) {
                $buildingAdminLevels = [EventRequest::LEVEL_NONE];
            } elseif ($hasProgramHead && !$hasAcademicHead) {
                $buildingAdminLevels = [EventRequest::LEVEL_1_PROGRAM_HEAD];
            } elseif (!$hasProgramHead && $hasAcademicHead) {
                $buildingAdminLevels = [EventRequest::LEVEL_2_ACADEMIC_HEAD];
            } else {
                $buildingAdminLevels = [EventRequest::LEVEL_2_ACADEMIC_HEAD];
            }

            $pendingEvents = EventRequest::where('status', 'Pending')
                ->whereIn('approval_level', $buildingAdminLevels)
                ->count();

            $pendingEventsList = EventRequest::with('user')
                ->where('status', 'Pending')
                ->whereIn('approval_level', $buildingAdminLevels)
                ->orderBy('event_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->limit(10)
                ->get();

            $approvedEvents = EventRequest::where('status', 'Approved')
                ->where('event_date', '>=', now()->toDateString())
                ->count();

            $upcomingEventsList = EventRequest::where('status', 'Approved')
                ->where('event_date', '>=', now()->toDateString())
                ->orderBy('event_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->limit(10)
                ->get();

            $totalConcerns = Concern::count();
            $pendingConcerns = Concern::where('status', '!=', 'Resolved')->count();

            $reportsQuery = Report::with('category');
            if (\Illuminate\Support\Facades\Schema::hasColumn('reports', 'is_deleted')) {
                $reportsQuery->where('is_deleted', false);
            }

            $reports = $reportsQuery->get();
            $supportsReportCount = Report::supportsReportCount();
            $reportWeight = fn ($report) => $supportsReportCount ? max(1, (int) ($report->report_count ?? 1)) : 1;
            $totalReports = $reports->sum($reportWeight);
            $resolvedReports = $reports->filter(fn ($report) => strtolower((string) $report->status) === 'resolved')->sum($reportWeight);
            $openReports = max(0, $totalReports - $resolvedReports);
            $hazardReports = $reports->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight);
            $resolutionRate = $totalReports > 0 ? round(($resolvedReports / $totalReports) * 100, 1) : 0;
            $statusOrder = ['Pending' => 1, 'Assigned' => 2, 'In Progress' => 3, 'Resolved' => 4];

            $statusStats = $reports
                ->groupBy(fn ($report) => $report->status ?: 'Unknown')
                ->map(fn ($items, $status) => ['status' => $status, 'count' => $items->sum($reportWeight)])
                ->sortBy(fn ($item) => $statusOrder[$item['status']] ?? 99)
                ->values();

            $monthStart = now()->startOfMonth()->subMonths(5);
            $trendStats = collect(range(0, 5))->map(function ($offset) use ($reports, $monthStart, $reportWeight) {
                $month = $monthStart->copy()->addMonths($offset);
                $monthKey = $month->format('Y-m');

                return [
                    'label' => $month->format('M Y'),
                    'reports' => $reports->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $monthKey)->sum($reportWeight),
                    'resolved' => $reports->filter(fn ($report) => $report->resolved_at && $report->resolved_at->format('Y-m') === $monthKey)->sum($reportWeight),
                ];
            })->values();

            $categoryStats = $reports
                ->groupBy(fn ($report) => optional($report->category)->name ?: 'Uncategorized')
                ->map(function ($items, $category) use ($reportWeight) {
                    return [
                        'category' => $category,
                        'total' => $items->sum($reportWeight),
                        'open' => $items->filter(fn ($report) => strtolower((string) $report->status) !== 'resolved')->sum($reportWeight),
                        'hazards' => $items->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight),
                    ];
                })
                ->sortByDesc('total')
                ->take(5)
                ->values();

            $locationStats = $reports
                ->filter(fn ($report) => filled($report->location))
                ->groupBy('location')
                ->map(function ($items, $location) use ($reportWeight) {
                    $open = $items->filter(fn ($report) => strtolower((string) $report->status) !== 'resolved')->sum($reportWeight);
                    $hazards = $items->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight);
                    $cost = (float) $items->sum(fn ($report) => (float) ($report->cost ?? 0));

                    return [
                        'location' => $location,
                        'open' => $open,
                        'hazards' => $hazards,
                        'risk' => ($open * 3) + ($hazards * 4) + min(20, (int) floor($cost / 1000)),
                    ];
                })
                ->sortByDesc('risk')
                ->values();

            $topCategory = $categoryStats->first();
            $topLocation = $locationStats->first();
            $dashboardAnalytics = [
                'total' => $totalReports,
                'open' => $openReports,
                'resolved' => $resolvedReports,
                'hazards' => $hazardReports,
                'resolution_rate' => $resolutionRate,
                'target_rate' => 85,
                'status' => $statusStats,
                'trend' => $trendStats,
                'categories' => $categoryStats,
                'top_category' => $topCategory,
                'top_location' => $topLocation,
            ];

            return view('dashboard.building-admin', compact(
                'pendingEvents', 
                'approvedEvents', 
                'totalConcerns', 
                'pendingConcerns', 
                'user', 
                'upcomingEventsList', 
                'pendingEventsList',
                'dashboardAnalytics'
            ));
        }

        // School Administrator, Academic Head, Program Head, Principal Assistant - modern Asana-style dashboard
        if (in_array($user->role, ['school_admin', 'academic_head', 'program_head', 'principal_assistant'])) {
            $pendingApprovalQuery = $this->pendingApprovalEventsFor($user);
            $pendingEvents = (clone $pendingApprovalQuery)->count();
            $pendingEventsList = $pendingApprovalQuery->limit(10)->get();

            $eventQuery2 = EventRequest::where('status', 'Approved')
                ->where('event_date', '>=', now()->toDateString());

            if ($user->role === 'program_head' && $user->department) {
                $eventQuery2->where('department', $user->department);
            }
            $approvedEvents = $eventQuery2->count();

            // Get list of upcoming approved events for display
            $upcomingEventsQuery = EventRequest::where('status', 'Approved')
                ->where('event_date', '>=', now()->toDateString())
                ->orderBy('event_date', 'asc')
                ->orderBy('start_time', 'asc');

            if ($user->role === 'program_head' && $user->department) {
                $upcomingEventsQuery->where('department', $user->department);
            }
            $upcomingEventsList = $upcomingEventsQuery->limit(10)->get();

            // Concerns - show all (no department filtering since concerns don't have department field)
            $totalConcerns = Concern::count();
            $pendingConcerns = Concern::where('status', '!=', 'Resolved')->count();

            return view('dashboard.principal', compact('pendingEvents', 'approvedEvents', 'totalConcerns', 'pendingConcerns', 'user', 'upcomingEventsList', 'pendingEventsList'));
        }

        // Faculty and other staff (maintenance, etc.) - faculty-style dashboard
        if ($user->role === 'faculty' || $user->role === 'maintenance') {
            // All event requests for the carousel (most recent first, limit 10)
            $myEventRequests = EventRequest::where('user_id', $user->id)
                ->where('faculty_deleted', false)
                ->where('status', 'Approved')
                ->orderBy('event_date', 'asc')
                ->limit(10)
                ->get();

            // Build calendar events from approved event requests
            $calendarEvents = $myEventRequests->map(function ($e) {
                return [
                    'title' => $e->title,
                    'start' => \Carbon\Carbon::parse($e->event_date)->toDateString(),
                ];
            })->values()->toArray();

            $approvedCount = $myEventRequests->count();

            $pendingCount = EventRequest::where('user_id', $user->id)
                ->where('faculty_deleted', false)
                ->where('status', 'Pending')
                ->count();

            return view('dashboard.faculty', compact('calendarEvents', 'pendingCount', 'approvedCount', 'myEventRequests'));
        }

        // Student dashboard - default
        $total = Concern::where('user_id', $user->id)->count();
        $pending = Concern::where('user_id', $user->id)->where('status', 'Pending')->count();
        $resolved = Concern::where('user_id', $user->id)->where('status', 'Resolved')->count();
        $inProgress = Concern::where('user_id', $user->id)->where('status', 'In Progress')->count();

        $concerns = Concern::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('total', 'pending', 'resolved', 'inProgress', 'concerns'));
    }

    private function pendingApprovalEventsFor($user)
    {
        $query = EventRequest::with('user')
            ->where('status', EventRequest::STATUS_PENDING)
            ->where('is_deleted', false)
            ->where('student_archived', false)
            ->where('faculty_archived', false)
            ->where('building_admin_archived', false)
            ->where('school_admin_archived', false)
            ->where('academic_head_archived', false)
            ->where('program_head_archived', false)
            ->where('mis_archived', false)
            ->where('maintenance_archived', false);

        if ($user->isProgramHead()) {
            $query->where('approval_level', EventRequest::LEVEL_1_PROGRAM_HEAD)
                ->where(function ($levelQuery) {
                    $levelQuery->whereNull('education_level')
                        ->orWhere('education_level', '!=', 'shs');
                });

            if ($user->department) {
                $query->where('department', $user->department);
            }
        } elseif ($user->isPrincipalAssistant()) {
            $query->where('approval_level', EventRequest::LEVEL_1_PROGRAM_HEAD)
                ->where('education_level', 'shs');
        } elseif ($user->isAcademicHead()) {
            $query->where('approval_level', EventRequest::LEVEL_2_ACADEMIC_HEAD);
        } elseif ($user->isSchoolAdmin() || $user->isAdmin()) {
            $query->where('approval_level', EventRequest::LEVEL_4_SCHOOL_ADMIN);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->orderBy('event_date', 'asc')
            ->orderBy('created_at', 'asc');
    }
}
