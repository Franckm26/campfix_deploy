@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Concern Reference: CFR-{{ date('Y') }}-{{ str_pad($concern->id, 5, '0', STR_PAD_LEFT) }}</h4>
                    <div>
                        <span class="badge bg-{{ 
                            $concern->priority == 'urgent' ? 'danger' : 
                            ($concern->priority == 'high' ? 'warning' : 
                            ($concern->priority == 'medium' ? 'info' : 'secondary'))
                        }} me-2">
                            {{ ucfirst($concern->priority) }} Priority
                        </span>
                        <span class="badge bg-{{ 
                            $concern->status == 'Resolved' ? 'success' : 
                            ($concern->status == 'In Progress' ? 'warning' : 
                            ($concern->status == 'Assigned' ? 'primary' : 'secondary'))
                        }}">
                            {{ $concern->status }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Category:</strong> {{ $concern->categoryRelation->name ?? 'N/A' }}</p>
                            <p><strong>Location:</strong> {{ $concern->location }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Reported:</strong> {{ $concern->created_at->format('M d, Y h:i A') }}</p>
                            @if(auth()->user()->role === 'mis')
                            <p>
                                <strong>Reported by:</strong> 
                                {{ $concern->is_anonymous ? 'Anonymous' : ($concern->user->name ?? 'Unknown') }}
                            </p>
                            @endif
                        </div>
                    </div>

                    @if($concern->image_path)
                        <div class="mb-3">
                            <p><strong>Photo:</strong></p>
                            <img src="{{ asset('storage/' . $concern->image_path) }}" 
                                alt="Concern photo" class="img-fluid rounded" style="max-width: 400px;">
                        </div>
                    @endif

                    <div class="mb-3">
                        <p><strong>Description:</strong></p>
                        <p class="card-text">{{ $concern->description }}</p>
                    </div>

                    @if($concern->resolution_notes)
                        <div class="alert alert-success">
                            <h5>Resolution Notes:</h5>
                            <p>{{ $concern->resolution_notes }}</p>
                            <small>Resolved on: {{ $concern->resolved_at->format('M d, Y h:i A') }}</small>
                        </div>
                    @endif

                    @if($concern->cost || $concern->damaged_part || $concern->replaced_part)
                        <div class="alert alert-info">
                            <h5>Repair Details:</h5>
                            @if($concern->cost)
                                <p><strong>Repair Cost:</strong> ₱{{ number_format($concern->cost, 2) }}</p>
                            @endif
                            @if($concern->damaged_part)
                                <p><strong>Damaged Part:</strong> {{ $concern->damaged_part }}</p>
                            @endif
                            @if($concern->replaced_part)
                                <p><strong>Replaced Part:</strong> {{ $concern->replaced_part }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status Update - For Admin or Maintenance -->
            @if(auth()->user()->role === 'mis' || auth()->user()->role === 'maintenance')
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Update Status</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ url('/update-status/' . $concern->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <select name="status" class="form-select">
                                    <option value="Pending" {{ $concern->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Assigned" {{ $concern->status == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="In Progress" {{ $concern->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Resolved" {{ $concern->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>

                        <hr>

                        <form action="{{ route('admin.resolution', $concern->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Resolution Notes</label>
                                <textarea name="resolution_notes" class="form-control" rows="3" 
                                    placeholder="Describe how it was resolved...">{{ $concern->resolution_notes }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Save Notes</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('concerns.my') }}" class="btn btn-secondary">Back to My Concerns</a>
    </div>
</div>
@endsection
