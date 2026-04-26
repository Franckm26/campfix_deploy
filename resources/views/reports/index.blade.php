@extends('layouts.app')

@section('page_title')
<h2>Reports Management</h2>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-4">
        <div class="col-md-6">
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-reports"
                    type="button" role="tab" aria-controls="active-reports" aria-selected="true">
                <i class="fas fa-list"></i> Active Reports
                @if(isset($reports) && $reports->count() > 0)
                    <span class="badge bg-primary ms-1">{{ $reports->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted-reports"
                    type="button" role="tab" aria-controls="deleted-reports" aria-selected="false">
                <i class="fas fa-trash"></i> Deleted Reports
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="reportTabContent">

        <!-- Active Reports Tab -->
        <div class="tab-pane fade show active" id="active-reports" role="tabpanel" aria-labelledby="active-tab">
            @if($reports->count() > 0)
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                     <tr>
                                         <th class="text-nowrap" style="min-width: 150px;">Title</th>
                                         <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                         <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                         <th class="text-nowrap" style="min-width: 100px;">Priority</th>
                                         <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                         <th class="text-nowrap" style="min-width: 100px;">Created</th>
                                         <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                     </tr>
                                 </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td>{{ $report->title }}</td>
                                            <td>{{ $report->category->name ?? 'N/A' }}</td>
                                            <td>{{ $report->location }}</td>
                                            <td>
                                                <span class="badge bg-{{ $report->severity === 'critical' ? 'danger' : ($report->severity === 'high' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($report->severity) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $report->status === 'Resolved' ? 'success' : ($report->status === 'In Progress' ? 'info' : 'primary') }}">
                                                    {{ $report->status }}
                                                </span>
                                            </td>
                                            <td>{{ $report->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-info bg-transparent border-0">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if($report->status !== 'Resolved')
                                                    <a href="{{ route('reports.edit', $report) }}" class="btn btn-sm btn-warning bg-transparent border-0">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('reports.archive', $report) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to archive this report?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary bg-transparent border-0">
                                                            <i class="fas fa-archive"></i> Archive
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('reports.destroy', $report) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this report?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <p>No active reports found.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Deleted Reports Tab -->
        <div class="tab-pane fade" id="deleted-reports" role="tabpanel" aria-labelledby="deleted-tab">
            <!-- Dropdown for auto-delete period -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0">Auto-delete reports deleted for more than:</h6>
                        </div>
                        <div class="col-md-4">
                            <select id="autoDeleteDays" class="form-select" onchange="updateAutoDeletePeriod(this.value)">
                                <option value="3">3 days</option>
                                <option value="7">7 days</option>
                                <option value="15" selected>15 days</option>
                                <option value="30">30 days</option>
                            </select>
                        </div>
                    </div>
                    <small class="text-muted">Reports deleted for longer than the selected period will be automatically removed.</small>
                </div>
            </div>

            <!-- Deleted Reports Content will be loaded here -->
            <div id="deletedReportsContent">
                <div class="card">
                    <div class="card-body text-center">
                        <p>Loading deleted reports...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function updateAutoDeletePeriod(days) {
    // Here you could implement AJAX call to update the auto-delete period setting
    // For now, just show a confirmation
    if (confirm('Set auto-delete period to ' + days + ' days? Reports deleted longer than this will be automatically removed.')) {
        // TODO: Implement AJAX call to save the setting
        alert('Auto-delete period updated to ' + days + ' days');
    } else {
        // Reset the dropdown
        document.getElementById('autoDeleteDays').value = '15';
    }
}

// Load deleted reports when tab is clicked
document.getElementById('deleted-tab').addEventListener('click', function() {
    // TODO: Implement AJAX loading of deleted reports
    document.getElementById('deletedReportsContent').innerHTML = `
        <div class="card">
            <div class="card-body text-center">
                <p>Deleted reports will be displayed here.</p>
                <p class="text-muted">Reports deleted for more than the selected period above will be automatically removed.</p>
            </div>
        </div>
    `;
});
</script>
@endsection