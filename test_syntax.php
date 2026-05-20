<?php
            ->where('title', '!=', '')
            ->select('location', 'title')
            ->distinct()
            ->get();

        foreach ($locationIssues as $li) {
            $loc   = $li->location;
            $issue = $li->title;
            $recent = Report::where('location', $loc)->where('title', $issue)
                ->where('created_at', '>=', now()->subMonths(3))->count();
            if ($recent < 1) continue;
            $allTimeCost = Report::where('location', $loc)->where('title', $issue)->sum('cost') ?? 0;
            $recentCost  = Report::where('location', $loc)->where('title', $issue)
                ->where('created_at', '>=', now()->subMonths(3))->sum('cost') ?? 0;
            $prior = Report::where('location', $loc)->where('title', $issue)
                ->whereBetween('created_at', [now()->subMonths(6), now()->subMonths(3)])->count();
            $severity   = $recent >= 3 ? 'critical' : ($recent >= 2 ? 'warning' : 'info');
            $alertTitle = $severity === 'critical' ? 'High Frequency Issue' : ($severity === 'warning' ? 'Recurring Issue' : 'Issue Detected');
            
            // Get monthly breakdown for the last 12 months
            $monthlyCosts = Report::where('location', $loc)
                ->where('title', $issue)
                ->where('status', 'Resolved')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count, SUM(cost) as cost")
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function($row) {
                    return [
                        'month' => \Carbon\Carbon::parse($row->month . '-01')->format('M Y'),
                        'count' => $row->count,
                        'cost' => $row->cost ?? 0
                    ];
                });
            
            // Get replacement threshold based on issue type
            $assetOriginalPrices = [
                'aircon' => 35000, 'air conditioner' => 35000, 'ac' => 35000,
                'chair' => 800, 'table' => 1500, 'desk' => 1500,
                'door' => 3000, 'window' => 2000,
                'light' => 200, 'bulb' => 200, 'lighting' => 200,
                'fan' => 1200, 'ceiling fan' => 1200,
                'computer' => 20000, 'pc' => 20000,
                'monitor' => 9000,
                'mouse' => 100, 'keyboard' => 150,
                'projector'=> 25000, 'whiteboard' => 1700,
            ];
            $key = strtolower(trim($issue));
            $replacementThreshold = $assetOriginalPrices[$key] ?? 10000;
            
            $trendAlerts->push([
                'location'    => $loc, 'top_issue' => $issue,
                'recent'      => $recent, 'prior' => $prior,
                'recent_cost' => $recentCost, 'all_time_cost' => $allTimeCost,
                'severity'    => $severity, 'alert_title' => $alertTitle,
                'updated_at'  => Report::where('location', $loc)->where('title', $issue)->latest()->value('updated_at'),
                'monthly_costs' => $monthlyCosts, 'replacement_threshold' => $replacementThreshold,
            ]);
        }
        $trendAlerts = $trendAlerts->sortByDesc('recent')->values();

        // Paginate trend alerts (10 per page)
        $perPage = 10;
        $currentPage = request('alerts_page', 1);
        $total = $trendAlerts->count();
        $offset = ($currentPage - 1) * $perPage;
        $paginatedAlerts = $trendAlerts->slice($offset, $perPage);
        $trendAlerts = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedAlerts,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'alerts_page']
        );

        // ========== ADVANCED ANALYTICS (Calculate before AJAX response) ==========
        
        try {
            // 2. Cost by Category Analysis
            $costByCategory = Report::with('category')
                ->whereNotNull('category_id')
                ->where('is_deleted', false)
                ->whereNotNull('resolved_at')
                ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->get()
                ->groupBy('category.name')
                ->map(function($group, $categoryName) {
                    return [
                        'category' => $categoryName ?: 'Uncategorized',
                        'count' => $group->count(),
                        'total_cost' => $group->sum('cost') ?? 0,
                        'avg_cost' => $group->avg('cost') ?? 0,
                        'percentage' => 0, // Will calculate after
                    ];
                })
                ->sortByDesc('total_cost')
                ->values();

