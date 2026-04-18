@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-md-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-2 py-md-3">
                    <h4 class="mb-0">Submit New Report</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="Brief title for the report" required>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location"
                                placeholder="e.g., Room 201, Building A, Cafeteria" required>
                        </div>

                        <div class="mb-3">
                            <label for="severity" class="form-label">Severity *</label>
                            <select class="form-select" id="severity" name="severity" required>
                                <option value="low">Low - Minor issue</option>
                                <option value="medium" selected>Medium - Moderate issue</option>
                                <option value="high">High - Major issue</option>
                                <option value="critical">Critical - Urgent issue</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="4" placeholder="Describe the issue in detail..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Upload Photo (Optional)</label>
                            <input type="file" class="form-control" id="photo" name="photo"
                                accept="image/*">
                            <small class="text-muted">Supported formats: JPEG, PNG, JPG, GIF (Max 2MB)</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Report
                            </button>
                            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection