@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-md-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-2 py-md-3">
                    <h4 class="mb-0">Edit Report</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.update', $report) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="" disabled>Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $report->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="{{ old('title', $report->title) }}" placeholder="Brief title for the report" required>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location"
                                value="{{ old('location', $report->location) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="severity" class="form-label">Severity *</label>
                            <select class="form-select" id="severity" name="severity" required>
                                <option value="low" {{ old('severity', $report->severity) == 'low' ? 'selected' : '' }}>Low - Minor issue</option>
                                <option value="medium" {{ old('severity', $report->severity) == 'medium' ? 'selected' : '' }}>Medium - Moderate issue</option>
                                <option value="high" {{ old('severity', $report->severity) == 'high' ? 'selected' : '' }}>High - Major issue</option>
                                <option value="critical" {{ old('severity', $report->severity) == 'critical' ? 'selected' : '' }}>Critical - Urgent issue</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="4" required>{{ old('description', $report->description) }}</textarea>
                        </div>

                        @if($report->photo_path)
                            <div class="mb-3">
                                <p><strong>Current Photo:</strong></p>
                                <img src="{{ asset('storage/' . $report->photo_path) }}"
                                    alt="Current photo" class="img-fluid rounded" style="max-width: 200px;">
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="photo" class="form-label">Upload New Photo (Optional)</label>
                            <input type="file" class="form-control" id="photo" name="photo"
                                accept="image/*">
                            <small class="text-muted">Leave empty to keep current photo. Supported formats: JPEG, PNG, JPG, GIF (Max 2MB)</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Report
                            </button>
                            <a href="{{ route('reports.show', $report) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection