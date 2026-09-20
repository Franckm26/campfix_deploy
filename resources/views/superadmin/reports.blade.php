@extends('superadmin.layout')

@section('page_title', 'All Reports')

@section('content')

<div class="sa-card mb-4">
    <form method="GET" action="{{ \App\Support\ProtectedRoute::url('superadmin.reports') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:200px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Title, description, location…" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
        </div>
        <div style="min-width:150px">
            <label class="sa-label">Status</label>
            <select name="status" class="sa-input">
                <option value="all"        {{ $status === 'all'        ? 'selected' : '' }}>All</option>
                <option value="Pending"    {{ $status === 'Pending'    ? 'selected' : '' }}>Pending</option>
                <option value="Assigned"   {{ $status === 'Assigned'   ? 'selected' : '' }}>Assigned</option>
                <option value="In Progress"{{ $status === 'In Progress'? 'selected' : '' }}>In Progress</option>
                <option value="Resolved"   {{ $status === 'Resolved'   ? 'selected' : '' }}>Resolved</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ \App\Support\ProtectedRoute::url('superadmin.reports') }}" class="sa-btn sa-btn-ghost">Reset</a>
        </div>
        <button type="button" class="sa-btn sa-btn-primary" style="margin-left:auto" onclick="openNewConcernModal()">
            <i class="fas fa-plus"></i> New Concern
        </button>
    </form>
</div>

<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }} reports
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Submitted By</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                @php
                    $statusColors = [
                        'Pending'     => 'sa-badge-yellow',
                        'Assigned'    => 'sa-badge-blue',
                        'In Progress' => 'sa-badge-blue',
                        'Resolved'    => 'sa-badge-green',
                    ];
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:500;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $report->title ?? 'Untitled' }}
                        </div>
                        @if($report->is_deleted)
                            <span class="sa-badge sa-badge-red" style="font-size:10px">Deleted</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $report->reported_by_name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $report->category->name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ Str::limit($report->location ?? '—', 30) }}</td>
                    <td><span class="sa-badge {{ $statusColors[$report->status] ?? 'sa-badge-gray' }}">{{ $report->status }}</span></td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $report->created_at->format('m/d/Y') }}</td>
                    <td>
                        <form method="POST" action="{{ \App\Support\ProtectedRoute::url('superadmin.reports.force-delete', $report->id) }}"
                              onsubmit="return confirm('Permanently delete this report? Cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Force Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--sa-muted);padding:32px">No reports found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        {{ $reports->links('vendor.pagination.superadmin') }}
    </div>
    @endif
</div>
@endsection

<!-- New Concern Modal -->
<div class="modal fade" id="newConcernModal" tabindex="-1" aria-labelledby="newConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newConcernModalLabel">Submit New Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newConcernForm" action="{{ route('concerns.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- hidden title populated from Issue dropdown --}}
                <input type="hidden" id="new_title" name="title">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="new_category_id" class="form-label">Category *</label>
                        <select class="form-select" id="new_category_id" name="category_id" required onchange="handleNewCategoryChange()">
                            <option value="" disabled selected>Select a category</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" data-name="{{ strtolower(trim($category->name)) }}" data-issues='@json($category->issues ?? [])'>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Issue dropdown -->
                    <div class="mb-3" id="new_issue_container" style="display: none;">
                        <label for="new_issue" class="form-label">Issue *</label>
                        <select class="form-select" id="new_issue" name="_issue">
                            <option value="" disabled selected>Select an issue</option>
                        </select>
                    </div>

                    <!-- Location (single flat dropdown for all categories) -->
                    <div class="mb-3" id="new_location_container" style="display: none;">
                        <label for="new_location" class="form-label">Location *</label>
                        <select class="form-select" id="new_location" name="location">
                            <option value="" disabled selected>Select a location</option>
                            @php
                            $facilities = \App\Models\Facility::all();
                            @endphp
                            @foreach($facilities->groupBy('type') as $type => $group)
                                <optgroup label="{{ ucfirst($type) }}">
                                    @foreach($group as $facility)
                                        <option value="{{ $facility->name }}">{{ $facility->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="new_problem_type_container">
                        <label for="new_description" class="form-label">Problem Type *</label>
                        <select class="form-select" id="new_description" name="description" disabled>
                            <option value="" disabled selected>Select an issue first</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="new_details" class="form-label">Additional description <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="new_details" name="details" rows="3" maxlength="2000" placeholder="Add useful details about the issue, if any."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="new_image" class="form-label">Upload Photo (Optional)</label>
                        <input type="file" class="form-control" id="new_image" name="image" 
                            accept="image/*">
                        <small class="text-muted d-block" style="font-size: 12px;">Supported formats: JPEG, PNG, JPG (Max 2MB)</small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="showReviewModal()">Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Review Concern Modal -->
<div class="modal fade" id="reviewConcernModal" tabindex="-1" aria-labelledby="reviewConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewConcernModalLabel">Review Your Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Category:</label>
                    <p id="review_category" class="mb-0"></p>
                </div>
                <div class="mb-3" id="review_issue_container" style="display: none;">
                    <label class="form-label fw-bold">Issue:</label>
                    <p id="review_issue" class="mb-0"></p>
                </div>
                <div class="mb-3" id="review_location_container" style="display: none;">
                    <label class="form-label fw-bold">Location:</label>
                    <p id="review_location" class="mb-0"></p>
                </div>
                <div class="mb-3" id="review_problem_type_container">
                    <label class="form-label fw-bold">Problem Type:</label>
                    <p id="review_description" class="mb-0" style="white-space: pre-wrap;"></p>
                </div>
                <div class="mb-3" id="review_image_container" style="display: none;">
                    <label class="form-label fw-bold">Photo:</label>
                    <div>
                        <img id="review_image" src="" alt="Preview" class="img-fluid" style="max-height: 200px; border-radius: 8px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="backToEdit()">Back to Edit</button>
                <button type="button" class="btn btn-primary" onclick="confirmSubmit()">Submit Concern</button>
            </div>
        </div>
    </div>
</div>

<script>
const concernProblemTypes = {
    aircon: ['Not working', 'Weak cooling', 'Leaking', 'Noisy', 'Bad smell', 'Remote/control issue'],
    'air conditioner': ['Not working', 'Weak cooling', 'Leaking', 'Noisy', 'Bad smell', 'Remote/control issue'],
    cleaning: ['Needs cleaning', 'Trash removal', 'Spill cleanup', 'Bad odor', 'Dusty area'],
    door: ['Broken lock', 'Damaged handle', 'Hard to open/close', 'Loose hinge', 'Misaligned door'],
    window: ['Broken glass', 'Stuck window', 'Loose frame', 'Leaking', 'Lock issue'],
    'electric outlet': ['No power', 'Loose socket', 'Sparking', 'Damaged cover', 'Burnt smell'],
    outlet: ['No power', 'Loose socket', 'Sparking', 'Damaged cover', 'Burnt smell'],
    lights: ['Not working', 'Flickering', 'Dim light', 'Broken switch', 'Exposed wiring'],
    light: ['Not working', 'Flickering', 'Dim light', 'Broken switch', 'Exposed wiring'],
    projector: ['Not working', 'No display', 'Blurry display', 'No sound', 'Remote/control issue'],
    internet: ['No connection', 'Slow connection', 'Intermittent connection', 'Router/access point issue'],
    wifi: ['No connection', 'Slow connection', 'Intermittent connection', 'Router/access point issue'],
    toilet: ['Clogged', 'Leaking', 'No water', 'Broken flush', 'Bad odor'],
    faucet: ['Leaking', 'No water', 'Low pressure', 'Broken handle', 'Loose fixture'],
    chair: ['Broken leg', 'Loose part', 'Damaged seat', 'Missing chair'],
    table: ['Broken leg', 'Loose part', 'Damaged surface', 'Unstable table'],
};

const defaultProblemTypes = ['Not working', 'Damaged', 'Leaking', 'Noisy', 'Missing part', 'Needs repair', 'Needs cleaning'];

function normalizeProblemKey(value) {
    return String(value || '').toLowerCase().trim();
}

function getProblemTypesForIssue(issue) {
    const key = normalizeProblemKey(issue);

    if (concernProblemTypes[key]) {
        return concernProblemTypes[key];
    }

    const partialMatch = Object.keys(concernProblemTypes).find(problemKey => key.includes(problemKey) || problemKey.includes(key));
    return partialMatch ? concernProblemTypes[partialMatch] : defaultProblemTypes;
}

function getIssueName(issue) {
    return typeof issue === 'string' ? issue : (issue && issue.name ? issue.name : '');
}

function getIssueProblemTypes(issue) {
    return typeof issue === 'object' && Array.isArray(issue.problem_types) ? issue.problem_types : [];
}

function populateProblemTypeSelect(selectElement, issue, selectedValue = '', explicitProblemTypes = null) {
    if (!selectElement) return;

    const problemTypeContainer = document.getElementById(selectElement.id + '_container')
        || (selectElement.id === 'new_description' ? document.getElementById('new_problem_type_container') : null);
    const hasExplicitProblemTypes = Array.isArray(explicitProblemTypes);
    const problemTypes = issue ? (hasExplicitProblemTypes ? explicitProblemTypes : getProblemTypesForIssue(issue)) : [];
    selectElement.innerHTML = '<option value="" disabled selected>' + (issue ? 'Select a problem type' : 'Select an issue first') + '</option>';
    selectElement.value = '';

    problemTypes.forEach(function(problemType) {
        const option = document.createElement('option');
        option.value = problemType;
        option.textContent = problemType;
        if (problemType === selectedValue) {
            option.selected = true;
        }
        selectElement.appendChild(option);
    });

    if (issue && problemTypes.length > 0) {
        if (problemTypeContainer) problemTypeContainer.style.display = 'block';
        selectElement.removeAttribute('disabled');
        selectElement.setAttribute('required', 'required');
    } else {
        if (problemTypeContainer) problemTypeContainer.style.display = 'none';
        selectElement.setAttribute('disabled', 'disabled');
        selectElement.removeAttribute('required');
    }
}

function handleNewCategoryChange() {
    const categorySelect = document.getElementById('new_category_id');
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;

    const categoryName = selectedOption.getAttribute('data-name');
    const categoryIssues = JSON.parse(selectedOption.getAttribute('data-issues') || '[]');
    
    const issueSelect = document.getElementById('new_issue');
    const issueContainer = document.getElementById('new_issue_container');
    const locationContainer = document.getElementById('new_location_container');
    const descEl = document.getElementById('new_description');

    // Populate issues
    issueSelect.innerHTML = '<option value="" disabled selected>Select an issue</option>';
    
    if (categoryIssues && categoryIssues.length > 0) {
        categoryIssues.forEach(function(issue) {
            const option = document.createElement('option');
            const issueName = getIssueName(issue);
            option.value = issueName;
            option.textContent = issueName;
            
            if (typeof issue === 'object' && issue.problem_types) {
                option.setAttribute('data-problem-types', JSON.stringify(issue.problem_types));
            }
            
            issueSelect.appendChild(option);
        });
        
        issueContainer.style.display = 'block';
        issueSelect.setAttribute('required', 'required');
        
        // Handle issue change
        issueSelect.onchange = function() {
            const selectedIssueOption = this.options[this.selectedIndex];
            if (selectedIssueOption && selectedIssueOption.value) {
                const issueName = selectedIssueOption.value;
                const explicitProblemTypes = selectedIssueOption.getAttribute('data-problem-types') 
                    ? JSON.parse(selectedIssueOption.getAttribute('data-problem-types'))
                    : null;
                
                populateProblemTypeSelect(descEl, issueName, '', explicitProblemTypes);
                
                // Set hidden title field
                document.getElementById('new_title').value = issueName;
                
                locationContainer.style.display = 'block';
                document.getElementById('new_location').setAttribute('required', 'required');
            }
        };
    } else {
        issueContainer.style.display = 'none';
        issueSelect.removeAttribute('required');
        locationContainer.style.display = 'block';
        document.getElementById('new_location').setAttribute('required', 'required');
        populateProblemTypeSelect(descEl, categoryName);
        document.getElementById('new_title').value = categoryName;
    }
}

function openNewConcernModal() {
    // Close any open modals to prevent conflicts
    const modalsToClose = ['viewConcernModal', 'editConcernModal', 'assignConcernModal', 'archiveModal', 'softDeleteModal', 'permanentDeleteModal', 'eventRequestModal', 'eventPreviewModal'];
    modalsToClose.forEach(modalId => {
        const modalEl = document.getElementById(modalId);
        if (modalEl) {
            const instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
        }
    });

    // Reset form
    const form = document.getElementById('newConcernForm');
    if (form) {
        form.reset();
        
        // Reset all dynamic elements
        document.getElementById('new_issue_container').style.display = 'none';
        document.getElementById('new_location_container').style.display = 'none';
        document.getElementById('new_problem_type_container').style.display = 'none';
        document.getElementById('new_issue').removeAttribute('required');
        document.getElementById('new_location').removeAttribute('required');
        document.getElementById('new_description').removeAttribute('required');
        
        // Reset problem type select
        const descEl = document.getElementById('new_description');
        populateProblemTypeSelect(descEl, '');
    }

    const modal = new bootstrap.Modal(document.getElementById('newConcernModal'));
    modal.show();
}

function showReviewModal() {
    // Validate form first
    const form = document.getElementById('newConcernForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Get form values
    const categorySelect = document.getElementById('new_category_id');
    const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text || '';
    
    const issueSelect = document.getElementById('new_issue');
    const issueText = issueSelect.options[issueSelect.selectedIndex]?.text || '';
    
    const locationSelect = document.getElementById('new_location');
    const locationText = locationSelect.options[locationSelect.selectedIndex]?.text || '';
    
    const problemTypeSelect = document.getElementById('new_description');
    const description = problemTypeSelect && !problemTypeSelect.disabled
        ? (problemTypeSelect.options[problemTypeSelect.selectedIndex]?.text || '')
        : '';
    
    const imageInput = document.getElementById('new_image');
    const imageFile = imageInput.files[0];

    // Populate review modal
    document.getElementById('review_category').textContent = categoryText;
    
    if (issueText && issueText !== 'Select an issue') {
        document.getElementById('review_issue').textContent = issueText;
        document.getElementById('review_issue_container').style.display = 'block';
    } else {
        document.getElementById('review_issue_container').style.display = 'none';
    }
    
    if (locationText && locationText !== 'Select a location') {
        document.getElementById('review_location').textContent = locationText;
        document.getElementById('review_location_container').style.display = 'block';
    } else {
        document.getElementById('review_location_container').style.display = 'none';
    }
    
    const reviewProblemTypeContainer = document.getElementById('review_problem_type_container');
    if (description) {
        document.getElementById('review_description').textContent = description;
        if (reviewProblemTypeContainer) reviewProblemTypeContainer.style.display = 'block';
    } else {
        document.getElementById('review_description').textContent = '';
        if (reviewProblemTypeContainer) reviewProblemTypeContainer.style.display = 'none';
    }
    
    // Handle image preview
    if (imageFile) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('review_image').src = e.target.result;
            document.getElementById('review_image_container').style.display = 'block';
        };
        reader.readAsDataURL(imageFile);
    } else {
        document.getElementById('review_image_container').style.display = 'none';
    }

    // Hide new concern modal and show review modal
    const newConcernModal = bootstrap.Modal.getInstance(document.getElementById('newConcernModal'));
    newConcernModal.hide();
    
    const reviewModal = new bootstrap.Modal(document.getElementById('reviewConcernModal'));
    reviewModal.show();
}

function backToEdit() {
    // Hide review modal and show new concern modal
    const reviewModal = bootstrap.Modal.getInstance(document.getElementById('reviewConcernModal'));
    reviewModal.hide();
    
    const newConcernModal = new bootstrap.Modal(document.getElementById('newConcernModal'));
    newConcernModal.show();
}

function confirmSubmit() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to submit this concern?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Trigger form submission event (not direct submit)
                const form = document.getElementById('newConcernForm');
                const event = new Event('submit', { cancelable: true, bubbles: true });
                form.dispatchEvent(event);
            }
        });
    } else {
        if (confirm('Are you sure you want to submit this concern?')) {
            const form = document.getElementById('newConcernForm');
            const event = new Event('submit', { cancelable: true, bubbles: true });
            form.dispatchEvent(event);
        }
    }
}

// Handle form submission with duplicate detection
document.addEventListener('DOMContentLoaded', function() {
    const concernForm = document.querySelector('#newConcernModal form');
    if (concernForm) {
        concernForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(concernForm);
            const submitBtn = document.querySelector('#reviewConcernModal .btn-primary');
            const originalHtml = submitBtn ? submitBtn.innerHTML : 'Submit Concern';
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            }
            
            fetch(concernForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('[DEBUG] Success response:', data);
                
                // Success - close modals and reload
                const newConcernModalEl = document.getElementById('newConcernModal');
                const reviewModalEl = document.getElementById('reviewConcernModal');
                
                if (newConcernModalEl) {
                    const instance = bootstrap.Modal.getInstance(newConcernModalEl);
                    if (instance) instance.hide();
                }
                
                if (reviewModalEl) {
                    const instance = bootstrap.Modal.getInstance(reviewModalEl);
                    if (instance) instance.hide();
                }
                
                // Show success message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Concern Submitted!',
                        text: 'Your concern has been submitted successfully.',
                        timer: 3000
                    });
                } else {
                    alert('Concern submitted successfully!');
                }
                
                // Reset form and reload page
                concernForm.reset();
                setTimeout(() => location.reload(), 2000);
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to submit concern. Please try again.'
                    });
                } else {
                    alert('Error: ' + (error.message || 'Failed to submit concern'));
                }
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            });
        });
    }
});
</script>