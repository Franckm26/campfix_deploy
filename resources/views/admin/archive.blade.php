@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-archive"></i> Archive</h2>
<p>View all archived items</p>
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

    <!-- Archived Concerns -->
    @if($archivedConcerns->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Archived Concerns ({{ $archivedConcerns->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedConcerns as $concern)
                            <tr>
                                <td>{{ $concern->title ?? 'No Title' }}</td>
                                <td>{{ $concern->categoryRelation->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $concern->priority == 'urgent' ? 'danger' : 
                                        ($concern->priority == 'high' ? 'warning' : 
                                        ($concern->priority == 'medium' ? 'info' : 'secondary'))
                                    }}">
                                        {{ ucfirst($concern->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $concern->status == 'Resolved' ? 'success' : 
                                        ($concern->status == 'In Progress' ? 'warning' : 
                                        ($concern->status == 'Assigned' ? 'primary' : 'secondary'))
                                    }}">
                                        {{ $concern->status }}
                                    </span>
                                </td>
                                <td>{{ $concern->is_anonymous ? 'Anonymous' : ($concern->user->name ?? 'Unknown') }}</td>
                                <td>{{ $concern->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.archive.restore') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="concern">
                                        <input type="hidden" name="id" value="{{ $concern->id }}">
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.concerns.softDelete', $concern->id) }}" class="d-inline"
                                          onsubmit="return confirm('Move this concern to deleted? It can be restored from the deleted folder.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Archived Events -->
    @if($archivedEvents->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calendar"></i> Archived Events ({{ $archivedEvents->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Event Date</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedEvents as $event)
                            <tr>
                                <td>{{ $event->title }}</td>
                                <td>{{ ucfirst($event->category) }}</td>
                                <td>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $event->status == 'Approved' ? 'success' : 
                                        ($event->status == 'Rejected' ? 'danger' : 
                                        ($event->status == 'Cancelled' ? 'secondary' : 'warning'))
                                    }}">
                                        {{ $event->status }}
                                    </span>
                                </td>
                                <td>{{ $event->user->name ?? 'N/A' }}</td>
                                <td>{{ $event->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.archive.restore') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="event">
                                        <input type="hidden" name="id" value="{{ $event->id }}">
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.events.softDelete', $event->id) }}" class="d-inline"
                                          onsubmit="return confirm('Move this event to deleted? It can be restored from the deleted folder.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Archived Reports -->
    @if($archivedReports->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Archived Reports ({{ $archivedReports->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedReports as $report)
                            <tr>
                                <td>{{ $report->title ?? 'No Title' }}</td>
                                <td>{{ $report->category->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $report->status == 'Resolved' ? 'success' : 
                                        ($report->status == 'In Progress' ? 'warning' : 
                                        ($report->status == 'Assigned' ? 'primary' : 'secondary'))
                                    }}">
                                        {{ $report->status }}
                                    </span>
                                </td>
                                <td>{{ $report->user->name ?? 'Unknown' }}</td>
                                <td>{{ $report->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.archive.restore') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="report">
                                        <input type="hidden" name="id" value="{{ $report->id }}">
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reports.softDelete', $report->id) }}" class="d-inline"
                                          onsubmit="return confirm('Move this report to deleted? It can be restored from the deleted folder.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Archived Facility Requests -->
    @if($archivedFacilities->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-tools"></i> Archived Facility Requests ({{ $archivedFacilities->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedFacilities as $facility)
                            <tr>
                                <td>{{ $facility->title ?? 'No Title' }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $facility->status == 'Approved' ? 'success' : 
                                        ($facility->status == 'Rejected' ? 'danger' : 
                                        ($facility->status == 'Cancelled' ? 'secondary' : 'warning'))
                                    }}">
                                        {{ $facility->status }}
                                    </span>
                                </td>
                                <td>{{ $facility->user->name ?? 'Unknown' }}</td>
                                <td>{{ $facility->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.archive.restore') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="facility">
                                        <input type="hidden" name="id" value="{{ $facility->id }}">
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.facilities.softDelete', $facility->id) }}" class="d-inline"
                                          onsubmit="return confirm('Move this facility request to deleted? It can be restored from the deleted folder.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($archivedConcerns->count() == 0 && $archivedEvents->count() == 0 && $archivedReports->count() == 0 && $archivedFacilities->count() == 0)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-archive fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No archived items found</h4>
            <p>There are no archived items in the system.</p>
        </div>
    </div>
    @endif
</div>
@endsection