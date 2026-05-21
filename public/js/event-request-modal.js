// Event Request Modal using SweetAlert2
function openEventRequestModal() {
    const facilities = window.facilitiesData || [];
    
    // Build facilities options HTML
    let facilitiesHTML = '<option value="">Select a location</option>';
    const groupedFacilities = {};
    
    facilities.forEach(facility => {
        if (!groupedFacilities[facility.type]) {
            groupedFacilities[facility.type] = [];
        }
        groupedFacilities[facility.type].push(facility);
    });
    
    Object.keys(groupedFacilities).forEach(type => {
        facilitiesHTML += `<optgroup label="${type.charAt(0).toUpperCase() + type.slice(1)}">`;
        groupedFacilities[type].forEach(facility => {
            facilitiesHTML += `<option value="${facility.name}">${facility.name}</option>`;
        });
        facilitiesHTML += '</optgroup>';
    });

    Swal.fire({
        title: '<i class="fas fa-calendar-plus"></i> Submit Event Request',
        html: `
            <form id="swalEventRequestForm" style="text-align: left;">
                <!-- Date and Time -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" id="swal_event_date" name="event_date" 
                            min="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start *</label>
                        <input type="time" class="form-control" id="swal_start_time" name="start_time" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End *</label>
                        <input type="time" class="form-control" id="swal_end_time" name="end_time" required>
                    </div>
                </div>

                <!-- Request Type and Intended User -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Request Type *</label>
                        <select class="form-select" id="swal_request_type" name="request_type" required>
                            <option value="">Select type</option>
                            <option value="Academic">Academic</option>
                            <option value="Non-Academic">Non-Academic</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Intended User *</label>
                        <select class="form-select" id="swal_education_level" name="education_level" required>
                            <option value="faculty" selected>Faculty</option>
                            <option value="tertiary">Tertiary</option>
                            <option value="shs">Senior High School</option>
                            <option value="staff">Staff</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Location -->
                <div class="mb-3" id="swal_location_container" style="display: none;">
                    <label class="form-label">Location *</label>
                    <select class="form-select" id="swal_area_of_use" name="area_of_use">
                        ${facilitiesHTML}
                    </select>
                </div>

                <!-- Department (for Academic requests) -->
                <div class="mb-3" id="swal_department_container" style="display: none;">
                    <label class="form-label">Department *</label>
                    <select class="form-select" id="swal_department" name="department">
                        <option value="">Select department</option>
                        <option value="GE">GE</option>
                        <option value="ICT">ICT</option>
                        <option value="Business Management">Business Management</option>
                        <option value="THM">THM</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control" id="swal_description" name="description" 
                        rows="4" placeholder="Describe the event purpose and details..." 
                        required maxlength="500"></textarea>
                    <small class="text-muted"><span id="swal_char_count">0</span> / 500</small>
                </div>

                <!-- Materials (Optional) -->
                <div class="mb-3">
                    <label class="form-label">Materials/Equipment Needed (Optional)</label>
                    <div id="swal_materials_container">
                        <div class="input-group mb-2">
                            <input type="number" class="form-control" placeholder="Qty" min="1" style="max-width: 80px;">
                            <input type="text" class="form-control" placeholder="Item name">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addSwalMaterial()">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </form>
        `,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-eye"></i> Preview',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        didOpen: () => {
            // Setup event listeners
            setupEventRequestFormListeners();
        },
        preConfirm: () => {
            return validateAndPreviewEventRequest();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showEventRequestPreview(result.value);
        }
    });
}

function setupEventRequestFormListeners() {
    // Request type change handler
    const requestType = document.getElementById('swal_request_type');
    const locationContainer = document.getElementById('swal_location_container');
    const departmentContainer = document.getElementById('swal_department_container');
    
    if (requestType) {
        requestType.addEventListener('change', function() {
            if (this.value) {
                locationContainer.style.display = 'block';
                if (this.value === 'Academic') {
                    departmentContainer.style.display = 'block';
                } else {
                    departmentContainer.style.display = 'none';
                }
            } else {
                locationContainer.style.display = 'none';
                departmentContainer.style.display = 'none';
            }
        });
    }

    // Character count for description
    const description = document.getElementById('swal_description');
    const charCount = document.getElementById('swal_char_count');
    if (description && charCount) {
        description.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
}

function addSwalMaterial() {
    const container = document.getElementById('swal_materials_container');
    const newRow = document.createElement('div');
    newRow.className = 'input-group mb-2';
    newRow.innerHTML = `
        <input type="number" class="form-control" placeholder="Qty" min="1" style="max-width: 80px;">
        <input type="text" class="form-control" placeholder="Item name">
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newRow);
}

function validateAndPreviewEventRequest() {
    const form = document.getElementById('swalEventRequestForm');
    const formData = {
        event_date: document.getElementById('swal_event_date').value,
        start_time: document.getElementById('swal_start_time').value,
        end_time: document.getElementById('swal_end_time').value,
        request_type: document.getElementById('swal_request_type').value,
        education_level: document.getElementById('swal_education_level').value,
        area_of_use: document.getElementById('swal_area_of_use').value,
        department: document.getElementById('swal_department').value,
        description: document.getElementById('swal_description').value,
        materials: []
    };

    // Validate required fields
    if (!formData.event_date || !formData.start_time || !formData.end_time || 
        !formData.request_type || !formData.description) {
        Swal.showValidationMessage('Please fill in all required fields');
        return false;
    }

    // Validate end time is after start time
    if (formData.end_time <= formData.start_time) {
        Swal.showValidationMessage('End time must be after start time');
        return false;
    }

    // Validate location is selected
    if (!formData.area_of_use) {
        Swal.showValidationMessage('Please select a location');
        return false;
    }

    // Validate department for Academic requests
    if (formData.request_type === 'Academic' && !formData.department) {
        Swal.showValidationMessage('Please select a department for Academic requests');
        return false;
    }

    // Collect materials
    const materialRows = document.querySelectorAll('#swal_materials_container .input-group');
    materialRows.forEach(row => {
        const qty = row.querySelector('input[type="number"]').value;
        const item = row.querySelector('input[type="text"]').value;
        if (qty && item) {
            formData.materials.push({ qty, item });
        }
    });

    return formData;
}

function showEventRequestPreview(formData) {
    const materialsHTML = formData.materials.length > 0 
        ? formData.materials.map(m => `<li>${m.qty}x ${m.item}</li>`).join('')
        : '<li class="text-muted">None</li>';

    Swal.fire({
        title: '<i class="fas fa-eye"></i> Event Request Preview',
        html: `
            <div style="text-align: left;">
                <h6 class="border-bottom pb-2 mb-3">Event Details</h6>
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Request Type:</div>
                    <div class="col-7">${formData.request_type}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Intended User:</div>
                    <div class="col-7">${formData.education_level.charAt(0).toUpperCase() + formData.education_level.slice(1)}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Location:</div>
                    <div class="col-7">${formData.area_of_use}</div>
                </div>
                ${formData.department ? `
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Department:</div>
                    <div class="col-7">${formData.department}</div>
                </div>
                ` : ''}
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Date:</div>
                    <div class="col-7">${new Date(formData.event_date).toLocaleDateString()}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Time:</div>
                    <div class="col-7">${formData.start_time} - ${formData.end_time}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Description:</div>
                    <div class="col-7">${formData.description}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-bold">Materials:</div>
                    <div class="col-7"><ul class="mb-0 ps-3">${materialsHTML}</ul></div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle"></i> <strong>Approval will be sent to:</strong> 
                    ${formData.request_type === 'Academic' ? 'Program Head, Academic Head, Building Admin, and School Administrator' : 'Building Admin and School Administrator'}
                </div>
            </div>
        `,
        width: '700px',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Submit',
        denyButtonText: '<i class="fas fa-edit"></i> Edit',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        denyButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
    }).then((result) => {
        if (result.isConfirmed) {
            submitEventRequest(formData);
        } else if (result.isDenied) {
            // Go back to edit
            setTimeout(() => openEventRequestModal(), 100);
        }
    });
}

function submitEventRequest(formData) {
    // Show loading
    Swal.fire({
        title: 'Submitting...',
        text: 'Please wait while we process your request',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create form data for submission
    const submitData = new FormData();
    submitData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    submitData.append('event_date', formData.event_date);
    submitData.append('start_time', formData.start_time);
    submitData.append('end_time', formData.end_time);
    submitData.append('request_type', formData.request_type);
    submitData.append('education_level', formData.education_level);
    submitData.append('category', 'Area Use');
    submitData.append('area_of_use', formData.area_of_use);
    submitData.append('location', formData.area_of_use);
    submitData.append('description', formData.description);
    if (formData.department) {
        submitData.append('department', formData.department);
    }
    
    // Add materials
    formData.materials.forEach((material, index) => {
        submitData.append(`materials[${index}][qty]`, material.qty);
        submitData.append(`materials[${index}][item]`, material.item);
    });

    // Submit via fetch
    fetch('/events', {
        method: 'POST',
        body: submitData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Event request submitted successfully!',
                confirmButtonColor: '#28a745'
            }).then(() => {
                // Redirect to appropriate view
                window.location.href = data.redirect || '/my-events?view=approved';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to submit event request',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while submitting your request',
            confirmButtonColor: '#dc3545'
        });
    });
}

// Make function globally available
window.openEventRequestModal = openEventRequestModal;
