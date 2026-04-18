@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Report Details</h4>
                    <div>
                        <span class="badge bg-{{ $report->severity === 'critical' ? 'danger' : ($report->severity === 'high' ? 'warning' : 'secondary') }} me-2">
                            {{ ucfirst($report->severity) }} Severity
                        </span>
                        <span class="badge bg-{{ $report->status === 'Resolved' ? 'success' : ($report->status === 'In Progress' ? 'warning' : 'primary') }}">
                            {{ $report->status }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                     <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Title:</strong> {{ $report->title }}</p>
                            <p><strong>Category:</strong> {{ $report->category->name ?? 'N/A' }}</p>
                            <p><strong>Location:</strong> {{ $report->location }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Reported:</strong> {{ $report->created_at->format('M d, Y h:i A') }}</p>
                            <p><strong>Reported by:</strong> {{ $report->user->name ?? 'Unknown' }}</p>
                        </div>
                    </div>

                    @if($report->photo_path)
                        <div class="mb-3">
                            <p><strong>Photo:</strong></p>
                            <img src="{{ asset('storage/' . $report->photo_path) }}"
                                alt="Report photo" class="img-fluid rounded" style="max-width: 400px;">
                        </div>
                    @endif

                    <div class="mb-3">
                        <p><strong>Description:</strong></p>
                        <p class="card-text">{{ $report->description }}</p>
                    </div>

                    @if($report->status === 'Resolved')
                        <div class="alert alert-success">
                            <h5>This report has been resolved.</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to My Reports</a>
        @if($report->user_id === auth()->id() && $report->status !== 'Resolved')
            <a href="{{ route('reports.edit', $report) }}" class="btn btn-warning ms-2">Edit Report</a>
        @endif
    </div>
</div>
@endsection