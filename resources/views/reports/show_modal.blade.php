<div class="modal-header">
    <h5 class="modal-title">Report Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="card border-0">
        <div class="card-header bg-light">
            <h6 class="mb-0 d-flex justify-content-between align-items-center">
                Report Details
                <div>
                    <span class="badge bg-{{ $report->severity === 'Critical' ? 'danger' : ($report->severity === 'High' ? 'warning' : 'secondary') }} me-2">
                        {{ ucfirst($report->severity) }} Severity
                    </span>
                    <span class="badge bg-{{ $report->status === 'Resolved' ? 'success' : ($report->status === 'In Progress' ? 'warning' : 'primary') }}">
                        {{ $report->status }}
                    </span>
                </div>
            </h6>
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $report->title }}</h5>

            <div class="row mb-3">
                <div class="col-md-6">
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
                        alt="Report photo" class="img-fluid rounded" style="max-width: 100%;">
                </div>
            @endif

            <div class="mb-3">
                <p><strong>Description:</strong></p>
                <p class="card-text">{{ $report->description }}</p>
            </div>

            @if($report->resolution_notes)
                <div class="alert alert-success">
                    <h5>Resolution Notes:</h5>
                    <p>{{ $report->resolution_notes }}</p>
                    <small>Resolved on: {{ $report->resolved_at->format('M d, Y h:i A') }}</small>
                </div>
            @endif

            @if($report->cost || $report->damaged_part || $report->replaced_part)
                <div class="alert alert-info">
                    <h5>Repair Details:</h5>
                    @if($report->cost)
                        <p><strong>Repair Cost:</strong> ₱{{ number_format($report->cost, 2) }}</p>
                    @endif
                    @if($report->damaged_part)
                        <p><strong>Damaged Part:</strong> {{ $report->damaged_part }}</p>
                    @endif
                    @if($report->replaced_part)
                        <p><strong>Replaced Part:</strong> {{ $report->replaced_part }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Status Update - For Admin or Maintenance -->
    @if(auth()->user()->role === 'mis' || auth()->user()->role === 'maintenance')
        <div class="card mt-3">
            <div class="card-header">
                <h6>Update Status</h6>
            </div>
            <div class="card-body">
                <form id="updateStatusForm" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $report->id }}">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Resolved" {{ $report->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <div class="mb-3" id="resolutionNotesGroup">
                        <label for="resolution_notes" class="form-label">Resolution Notes</label>
                        <textarea class="form-control" id="resolution_notes" name="resolution_notes" rows="3" placeholder="Describe how the issue was resolved...">{{ $report->resolution_notes }}</textarea>
                    </div>
                    <div id="maintenanceFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cost" class="form-label">Cost (PHP)</label>
                                <input type="number" class="form-control" id="cost" name="cost" step="0.01" min="0" placeholder="0.00" value="{{ $report->cost }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="damaged_part" class="form-label">Damaged Part</label>
                                <input type="text" class="form-control" id="damaged_part" name="damaged_part" placeholder="What part was damaged?" value="{{ $report->damaged_part }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="replaced_part" class="form-label">Replaced With</label>
                            <input type="text" class="form-control" id="replaced_part" name="replaced_part" placeholder="What was it replaced with?" value="{{ $report->replaced_part }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>

                <hr>

                <form action="{{ route('admin.report-resolution', $report->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes</label>
                        <textarea name="resolution_notes" class="form-control" rows="3"
                            placeholder="Describe how it was resolved...">{{ $report->resolution_notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Save Notes</button>
                </form>
            </div>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>