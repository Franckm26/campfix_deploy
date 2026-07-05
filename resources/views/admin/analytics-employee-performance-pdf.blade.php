<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Employee Performance Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 22px; }
        .header { border-bottom: 3px solid #0d6efd; padding-bottom: 12px; margin-bottom: 18px; }
        h1 { font-size: 20px; margin: 0 0 4px; color: #0d1b2a; }
        .muted { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; vertical-align: top; }
        th { background: #f8fafc; font-weight: 700; color: #0f172a; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-weight: 700; font-size: 10px; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-cyan { background: #cffafe; color: #0e7490; }
        .badge-purple { background: #ede9fe; color: #6d28d9; }
        .badge-yellow { background: #fef3c7; color: #b45309; }
        .badge-gray { background: #e5e7eb; color: #4b5563; }
        .employee-detail { page-break-inside: avoid; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; margin-bottom: 14px; }
        .employee-title { font-size: 15px; font-weight: 800; margin-bottom: 4px; color: #111827; }
        .metric-grid { width: 100%; margin-top: 10px; margin-bottom: 10px; }
        .metric-grid td { width: 16.66%; text-align: center; background: #f8fafc; }
        .metric-label { font-size: 9px; color: #64748b; }
        .metric-value { font-size: 14px; font-weight: 800; margin-top: 3px; }
        ul { margin: 6px 0 0 18px; padding: 0; }
        .small-table th, .small-table td { font-size: 9px; padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Employee Performance Report</h1>
        <div class="muted">Analytics period: {{ $dateRange }}</div>
        <div class="muted">Generated: {{ now()->format('M d, Y h:i A') }}</div>
    </div>

    <h2 style="font-size:15px;margin:0 0 8px;">Performance Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Position</th>
                <th>Department</th>
                <th class="text-center">Assigned</th>
                <th class="text-center">Resolved</th>
                <th class="text-center">Active</th>
                <th class="text-center">Performance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employeePerformanceStats as $employee)
            @php
                $badgeClass = match($employee['performance_status']) {
                    'Excellent' => 'badge-blue',
                    'Very Good' => 'badge-cyan',
                    'Good' => 'badge-purple',
                    'Needs Monitoring' => 'badge-yellow',
                    default => 'badge-gray',
                };
            @endphp
            <tr>
                <td><strong>{{ $employee['name'] }}</strong><br><span class="muted">{{ $employee['email'] ?? 'N/A' }}</span></td>
                <td>{{ $employee['position'] }}</td>
                <td>{{ $employee['department'] }}</td>
                <td class="text-center">{{ $employee['assigned_count'] }}</td>
                <td class="text-center">{{ $employee['resolved_count'] }}</td>
                <td class="text-center">{{ $employee['active_count'] }}</td>
                <td class="text-center"><span class="badge badge-green">{{ $employee['performance_score'] }}%</span></td>
                <td><span class="badge {{ $badgeClass }}">{{ $employee['performance_status'] }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No employee performance data found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2 style="font-size:15px;margin:10px 0 8px;">Employee Details</h2>
    @foreach($employeePerformanceStats as $employee)
    <div class="employee-detail">
        <div class="employee-title">{{ $employee['name'] }}</div>
        <div class="muted">{{ $employee['position'] }} · {{ $employee['department'] }}</div>
        <div style="margin-top:6px;">
            <strong>Email:</strong> {{ $employee['email'] ?? 'N/A' }} &nbsp;
            <strong>Phone:</strong> {{ $employee['phone'] ?? 'N/A' }}
        </div>

        <table class="metric-grid">
            <tr>
                <td><div class="metric-label">Assigned</div><div class="metric-value">{{ $employee['assigned_count'] }}</div></td>
                <td><div class="metric-label">Resolved</div><div class="metric-value">{{ $employee['resolved_count'] }}</div></td>
                <td><div class="metric-label">Active</div><div class="metric-value">{{ $employee['active_count'] }}</div></td>
                <td><div class="metric-label">Completion</div><div class="metric-value">{{ $employee['completion_rate'] }}%</div></td>
                <td><div class="metric-label">Avg Resolution</div><div class="metric-value">{{ $employee['avg_resolution_hours'] !== null ? number_format($employee['avg_resolution_hours'], 1) . 'h' : 'N/A' }}</div></td>
                <td><div class="metric-label">Performance</div><div class="metric-value">{{ $employee['performance_score'] }}%</div></td>
            </tr>
        </table>

        <div><strong>Total Repair Cost Handled:</strong> ₱{{ number_format($employee['total_cost_handled'], 2) }}</div>
        <div style="margin-top:8px;"><strong>Notes</strong></div>
        <ul>
            @foreach($employee['notes'] as $note)
            <li>{{ $note }}</li>
            @endforeach
        </ul>

        <div style="margin-top:10px;"><strong>Recent Tickets</strong></div>
        <table class="small-table">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Issue</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Cost</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee['recent_tickets'] as $ticket)
                <tr>
                    <td>{{ $ticket['ticket'] }}</td>
                    <td>{{ $ticket['issue'] }}</td>
                    <td>{{ $ticket['location'] }}</td>
                    <td>{{ $ticket['status'] }}</td>
                    <td class="text-right">₱{{ number_format($ticket['cost'], 2) }}</td>
                    <td>{{ $ticket['created_at'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No recent tickets found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endforeach
</body>
</html>
