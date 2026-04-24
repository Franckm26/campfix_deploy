@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-md-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-2 py-md-3">
                    <h4 class="mb-0">Submit New Concern</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('concerns.store') }}" method="POST" enctype="multipart/form-data" id="concernForm">
                        @csrf

                        <div class="mb-2 mb-md-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="" disabled selected>Select a category</option>
                                @foreach($categories as $category)
                                    @if(auth()->user()->role === 'student' && strtolower($category->name) === 'rooms')
                                        @continue
                                    @endif
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2 mb-md-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                placeholder="e.g., Room 201, Building A, Cafeteria" required>
                        </div>

                        <div class="mb-2 mb-md-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" 
                                rows="3" rows-md="4" placeholder="Describe the problem in detail..." required></textarea>
                        </div>

                        <div class="mb-2 mb-md-3">
                            <label for="image" class="form-label">Upload Photo (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" 
                                accept="image/*">
                            <small class="text-muted d-block" style="font-size: 12px;">Supported formats: JPEG, PNG, JPG, GIF (Max 2MB)</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#concernPreviewModal" id="previewBtn" disabled>
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Concern
                            </button>
                            <a href="{{ route('concerns.my') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Preview Modal -->
<div class="modal fade" id="concernPreviewModal" tabindex="-1" aria-labelledby="concernPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="concernPreviewModalLabel"><i class="fas fa-eye"></i> Concern Request Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="border-bottom pb-2 mb-3">Concern Details</h6>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Title:</div>
                    <div class="col-md-8" id="preview_title">-</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Category:</div>
                    <div class="col-md-8" id="preview_category">-</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Location:</div>
                    <div class="col-md-8" id="preview_location">-</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Description:</div>
                    <div class="col-md-8" id="preview_description">-</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Photo:</div>
                    <div class="col-md-8" id="preview_image">No photo uploaded</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button type="submit" form="concernForm" class="btn btn-primary">
                    <i class="fas fa-check"></i> Submit Concern
                </button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview button click handler
    var previewBtn = document.getElementById('previewBtn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get form values
            var title = document.getElementById('title').value.trim();
            var categoryId = document.getElementById('category_id').value;
            var categorySelect = document.getElementById('category_id');
            var categoryDisplay = categorySelect.options[categorySelect.selectedIndex]?.text || '-';
            var location = document.getElementById('location').value.trim();
            var description = document.getElementById('description').value.trim();
            var imageInput = document.getElementById('image');
            
            // Populate preview fields
            document.getElementById('preview_title').textContent = title || 'No title provided';
            document.getElementById('preview_category').textContent = categoryDisplay;
            document.getElementById('preview_location').textContent = location || '-';
            document.getElementById('preview_description').textContent = description || '-';
            
            // Handle image preview
            if (imageInput && imageInput.files && imageInput.files.length > 0) {
                var fileName = imageInput.files[0].name;
                document.getElementById('preview_image').textContent = fileName + ' (Ready to upload)';
            } else {
                document.getElementById('preview_image').textContent = 'No photo uploaded';
            }
        });
    }
    
    // Function to check if required fields are filled and enable/disable Preview button
    function updatePreviewButtonState() {
        var categoryId = document.getElementById('category_id');
        var location = document.getElementById('location');
        var description = document.getElementById('description');
        var previewBtn = document.getElementById('previewBtn');
        
        var isValid = true;
        
        // Check if required fields are filled
        if (!categoryId || !categoryId.value) isValid = false;
        if (!location || !location.value.trim() || location.value.trim().length < 3) isValid = false;
        if (!description || !description.value.trim() || description.value.trim().length < 10) isValid = false;
        
        // Enable/disable Preview button
        if (previewBtn) {
            previewBtn.disabled = !isValid;
            if (isValid) {
                previewBtn.classList.remove('btn-secondary');
                previewBtn.classList.add('btn-info');
            } else {
                previewBtn.classList.remove('btn-info');
                previewBtn.classList.add('btn-secondary');
            }
        }
    }
    
    // Add event listeners to all required fields to update Preview button state
    var requiredFieldIds = ['category_id', 'location', 'description'];
    requiredFieldIds.forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updatePreviewButtonState);
            field.addEventListener('change', updatePreviewButtonState);
        }
    });
    
    // Initial check
    updatePreviewButtonState();
});
</script>
@endsection
