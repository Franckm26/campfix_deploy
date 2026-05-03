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

            // Analytics data for graphs
            $baseQuery = Report::whereNotNull('location')
                ->where('location', '!=', '')
                ->where('status', 'Resolved');

            $totalCost = (clone $baseQuery)->sum('cost') ?? 0;

            // Location stats for pie chart
            $locationStats = (clone $baseQuery)
                ->select('location')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
                ->groupBy('location')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            $chartLocations = $locationStats->pluck('location')->toArray();
            $chartCounts = $locationStats->pluck('count')->toArray();
            $chartCosts = $locationStats->pluck('total_cost')->toArray();

            // Monthly stats for line chart - last 6 months
            $monthlyStats = Report::where('created_at', '>=', now()->subMonths(6))
                ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month")
                ->selectRaw('title')
                ->selectRaw('COUNT(*) as total_count')
                ->groupBy('month', 'title')
                ->orderBy('month')
                ->get();

            return view('dashboard.building-admin', compact(
                'pendingEvents', 
                'approvedEvents', 
                'totalConcerns', 
                'pendingConcerns', 
                'user', 
                'upcomingEventsList', 
                'pendingEventsList',
                'totalCost',
                'chartLocations',
                'chartCounts',
                'chartCosts',
                'monthlyStats'
            ));
        }

        // School Administrator, Academic Head, Program Head, Principal Assistant - modern Asana-style dashboard
        if (in_array($user->role, ['school_admin', 'academic_head', 'program_head', 'principal_assistant'])) {
            // Principal dashboard - show pending approvals and events
            // For Program Head, filter by their department for events only
            $eventQuery = EventRequest::where('status', 'Pending');

            if ($user->role === 'program_head' && $user->department) {
                $eventQuery->where('department', $user->department);
            }
            $pendingEvents = $eventQuery->count();

            // Get list of pending events for the task list
            $pendingEventsListQuery = EventRequest::with('user')
                ->where('status', 'Pending')
                ->orderBy('event_date', 'asc')
                ->orderBy('created_at', 'asc');

            if ($user->role === 'program_head' && $user->department) {
                $pendingEventsListQuery->where('department', $user->department);
            }
            $pendingEventsList = $pendingEventsListQuery->limit(10)->get();

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

        if ($user->role === 'faculty') {
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
}
