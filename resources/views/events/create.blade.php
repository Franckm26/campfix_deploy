@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-calendar-plus"></i> My Event Request</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('events.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="event_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date"
                                min="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="education_level" class="form-label">Level *</label>
                                <select class="form-select" id="education_level" name="education_level" required>
                                    <option value="tertiary" selected>Tertiary</option>
                                    <option value="shs">Senior High School</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Request Type *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select category</option>
                                    <option value="Area Use">Area Use</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Area of Use (only shows when Area Use is selected) -->
                        <div class="mb-3" id="area_of_use_container" style="display: none;">
                            <label for="area_of_use" class="form-label">Location *</label>
                            <select class="form-select" id="area_of_use" name="area_of_use">
                                <option value="">Select area of use</option>
                                <option value="Room">Room</option>
                                <option value="Court">Court</option>
                                <option value="AVR">AVR</option>
                                <option value="Library">Library</option>
                                <option value="Open Lobby">Open Lobby</option>
                                <option value="Computer Laboratory">Computer Laboratory</option>
                                <option value="Kitchen">Kitchen</option>
                            </select>
                        </div>

                        <!-- Room Number (only shows when Room is selected in Area of Use) -->
                        <div class="mb-3" id="room_number_container" style="display: none;">
                            <label for="room_number" class="form-label">Room Number *</label>
                            <select class="form-select" id="room_number" name="room_number">
                                <option value="">Select room number</option>
                                <option value="101">101</option>
                                <option value="102">102</option>
                                <option value="103">103</option>
                                <option value="104">104</option>
                                <option value="105">105</option>
                                <option value="106">106</option>
                                <option value="107">107</option>
                                <option value="108">108</option>
                                <option value="109">109</option>
                                <option value="110">110</option>
                                <option value="111">111</option>
                                <option value="112">112</option>
                                <option value="113">113</option>
                                <option value="114">114</option>
                                <option value="115">115</option>
                                <option value="201">201</option>
                                <option value="202">202</option>
                                <option value="203">203</option>
                                <option value="204">204</option>
                                <option value="205">205</option>
                                <option value="206">206</option>
                                <option value="207">207</option>
                                <option value="208">208</option>
                                <option value="209">209</option>
                                <option value="210">210</option>
                                <option value="211">211</option>
                                <option value="212">212</option>
                                <option value="213">213</option>
                                <option value="214">214</option>
                                <option value="215">215</option>
                                <option value="301">301</option>
                                <option value="302">302</option>
                                <option value="303">303</option>
                                <option value="304">304</option>
                                <option value="305">305</option>
                                <option value="306">306</option>
                                <option value="307">307</option>
                                <option value="308">308</option>
                                <option value="309">309</option>
                                <option value="310">310</option>
                                <option value="311">311</option>
                                <option value="312">312</option>
                                <option value="313">313</option>
                                <option value="314">314</option>
                                <option value="315">315</option>
                                <option value="401">401</option>
                                <option value="402">402</option>
                                <option value="403">403</option>
                                <option value="404">404</option>
                                <option value="405">405</option>
                                <option value="406">406</option>
                                <option value="407">407</option>
                                <option value="408">408</option>
                                <option value="409">409</option>
                                <option value="410">410</option>
                                <option value="411">411</option>
                                <option value="412">412</option>
                                <option value="413">413</option>
                                <option value="414">414</option>
                                <option value="415">415</option>
                                <option value="501">501</option>
                                <option value="502">502</option>
                                <option value="503">503</option>
                                <option value="504">504</option>
                                <option value="505">505</option>
                                <option value="506">506</option>
                                <option value="507">507</option>
                                <option value="508">508</option>
                                <option value="509">509</option>
                                <option value="510">510</option>
                                <option value="511">511</option>
                                <option value="Suite Room">Suite Room</option>
                                <option value="Kitchen 1">Kitchen 1</option>
                                <option value="Kitchen 2">Kitchen 2</option>
                                <option value="Bar">Bar</option>
                                <option value="M01">M01</option>
                            </select>
                        </div>

                        <!-- Department (only shows when Room is selected in Area of Use) -->
                        <div class="mb-3" id="department_container" style="display: none;">
                            <label for="department" class="form-label">Department *</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Select department</option>
                                <option value="GE">GE</option>
                                <option value="ICT">ICT</option>
                                <option value="Business Management">Business Management</option>
                                <option value="THM">THM</option>
                            </select>
                        </div>

                        <!-- Court Type (only shows when Court is selected in Area of Use) -->
                        <div class="mb-3" id="court_type_container" style="display: none;">
                            <label for="court_type" class="form-label">Request Category *</label>
                            <select class="form-select" id="court_type" name="court_type">
                                <option value="">Select type</option>
                                <option value="Non-academic">Non-academic</option>
                                <option value="Academic">Academic</option>
                            </select>
                        </div>

                        <!-- Court Purpose (only shows when Court Type is selected) -->
                        <div class="mb-3" id="court_purpose_container" style="display: none;">
                            <label for="court_purpose" class="form-label">Purpose *</label>
                            <input type="text" class="form-control" id="court_purpose" name="court_purpose"
                                placeholder="Describe the purpose for court use">
                        </div>

                        <!-- AVR Selection (only shows when AVR is selected in Area of Use) -->
                        <div class="mb-3" id="avr_selection_container" style="display: none;">
                            <label for="avr_selection" class="form-label">AVR Selection *</label>
                            <select class="form-select" id="avr_selection" name="avr_selection">
                                <option value="">Select AVR</option>
                                <option value="AVR 1">AVR 1</option>
                                <option value="AVR 2">AVR 2</option>
                            </select>
                        </div>

                        <!-- AVR Request Category (only shows when AVR Selection is selected) -->
                        <div class="mb-3" id="avr_request_category_container" style="display: none;">
                            <label for="avr_request_category" class="form-label">Request Category *</label>
                            <select class="form-select" id="avr_request_category" name="avr_request_category">
                                <option value="">Select category</option>
                                <option value="Non-academic">Non-academic</option>
                                <option value="Academic">Academic</option>
                            </select>
                        </div>


                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" 
                                rows="4" placeholder="Describe the event purpose and details..." required></textarea>
                        </div>

                        <!-- Materials/Equipment Needed (Optional) -->
                        <div class="mb-3">
                            <label class="form-label">Materials/Equipment Needed (Optional)</label>
                            <table class="table table-bordered" id="materialsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Qty</th>
                                        <th>Item</th>
                                        <th>Purpose</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="number" class="form-control" name="materials[0][qty]" min="1" placeholder="1"></td>
                                        <td><input type="text" class="form-control" name="materials[0][item]" placeholder="e.g., Projector, Chair, etc."></td>
                                        <td><input type="text" class="form-control" name="materials[0][purpose]" placeholder="e.g., For presentation"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeMaterialRow(this)"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addMaterialRow()">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Submit for Approval</button>
                            <a href="/dashboard" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>

    // Category change handler for cascading dropdowns
    document.getElementById('category').addEventListener('change', function() {
        var areaOfUseContainer = document.getElementById('area_of_use_container');
        var roomNumberContainer = document.getElementById('room_number_container');
        var departmentContainer = document.getElementById('department_container');
        var areaOfUseSelect = document.getElementById('area_of_use');

        if (this.value === 'Area Use') {
            areaOfUseContainer.style.display = 'block';
            areaOfUseSelect.required = true;
        } else {
            areaOfUseContainer.style.display = 'none';
            roomNumberContainer.style.display = 'none';
            departmentContainer.style.display = 'none';
            areaOfUseSelect.required = false;
            areaOfUseSelect.value = '';
            document.getElementById('room_number').required = false;
            document.getElementById('room_number').value = '';
        }
    });

    // Area of Use change handler
    document.getElementById('area_of_use').addEventListener('change', function() {
        var roomNumberContainer = document.getElementById('room_number_container');
        var departmentContainer = document.getElementById('department_container');
        var courtTypeContainer = document.getElementById('court_type_container');
        var courtPurposeContainer = document.getElementById('court_purpose_container');
        var avrSelectionContainer = document.getElementById('avr_selection_container');
        var avrRequestCategoryContainer = document.getElementById('avr_request_category_container');
        var roomNumberSelect = document.getElementById('room_number');
        var departmentSelect = document.getElementById('department');
        var courtTypeSelect = document.getElementById('court_type');
        var courtPurposeInput = document.getElementById('court_purpose');
        var avrSelectionSelect = document.getElementById('avr_selection');
        var avrRequestCategorySelect = document.getElementById('avr_request_category');

        // Clear court availability message
        var existingCourtMsg = document.getElementById('court_availability_message');
        if (existingCourtMsg) {
            existingCourtMsg.remove();
        }

        // Clear AVR availability message
        var existingAvrMsg = document.getElementById('avr_availability_message');
        if (existingAvrMsg) {
            existingAvrMsg.remove();
        }

        if (this.value === 'Room') {
            roomNumberContainer.style.display = 'block';
            departmentContainer.style.display = 'block';
            courtTypeContainer.style.display = 'none';
            courtPurposeContainer.style.display = 'none';
            avrSelectionContainer.style.display = 'none';
            avrRequestCategoryContainer.style.display = 'none';
            roomNumberSelect.required = true;
            departmentSelect.required = true;
            courtTypeSelect.required = false;
            courtPurposeInput.required = false;
            avrSelectionSelect.required = false;
            avrRequestCategorySelect.required = false;
            courtTypeSelect.value = '';
            courtPurposeInput.value = '';
            avrSelectionSelect.value = '';
            avrRequestCategorySelect.value = '';
        } else if (this.value === 'Court') {
            roomNumberContainer.style.display = 'none';
            departmentContainer.style.display = 'none';
            courtTypeContainer.style.display = 'block';
            courtPurposeContainer.style.display = 'none';
            avrSelectionContainer.style.display = 'none';
            avrRequestCategoryContainer.style.display = 'none';
            roomNumberSelect.required = false;
            departmentSelect.required = false;
            courtTypeSelect.required = true;
            courtPurposeInput.required = false;
            avrSelectionSelect.required = false;
            avrRequestCategorySelect.required = false;
            roomNumberSelect.value = '';
            departmentSelect.value = '';
            courtPurposeInput.value = '';
            avrSelectionSelect.value = '';
            avrRequestCategorySelect.value = '';
        } else if (this.value === 'AVR') {
            roomNumberContainer.style.display = 'none';
            departmentContainer.style.display = 'none';
            courtTypeContainer.style.display = 'none';
            courtPurposeContainer.style.display = 'none';
            avrSelectionContainer.style.display = 'block';
            avrRequestCategoryContainer.style.display = 'none';
            roomNumberSelect.required = false;
            departmentSelect.required = false;
            courtTypeSelect.required = false;
            courtPurposeInput.required = false;
            avrSelectionSelect.required = true;
            avrRequestCategorySelect.required = false;
            roomNumberSelect.value = '';
            departmentSelect.value = '';
            courtTypeSelect.value = '';
            courtPurposeInput.value = '';
            avrRequestCategorySelect.value = '';
        } else {
            roomNumberContainer.style.display = 'none';
            departmentContainer.style.display = 'none';
            courtTypeContainer.style.display = 'none';
            courtPurposeContainer.style.display = 'none';
            avrSelectionContainer.style.display = 'none';
            avrRequestCategoryContainer.style.display = 'none';
            roomNumberSelect.required = false;
            departmentSelect.required = false;
            courtTypeSelect.required = false;
            courtPurposeInput.required = false;
            avrSelectionSelect.required = false;
            avrRequestCategorySelect.required = false;
            roomNumberSelect.value = '';
            departmentSelect.value = '';
            courtTypeSelect.value = '';
            courtPurposeInput.value = '';
            avrSelectionSelect.value = '';
            avrRequestCategorySelect.value = '';
        }
    });

    // AVR Selection change handler
    document.getElementById('avr_selection').addEventListener('change', function() {
        var avrRequestCategoryContainer = document.getElementById('avr_request_category_container');
        var avrRequestCategorySelect = document.getElementById('avr_request_category');

        if (this.value) {
            avrRequestCategoryContainer.style.display = 'block';
            avrRequestCategorySelect.required = true;
        } else {
            avrRequestCategoryContainer.style.display = 'none';
            avrRequestCategorySelect.required = false;
            avrRequestCategorySelect.value = '';
        }
    });

    // Court Type change handler
    document.getElementById('court_type').addEventListener('change', function() {
        var courtPurposeContainer = document.getElementById('court_purpose_container');
        var courtPurposeInput = document.getElementById('court_purpose');
        var departmentContainer = document.getElementById('department_container');
        var departmentSelect = document.getElementById('department');

        if (this.value) {
            courtPurposeContainer.style.display = 'block';
            courtPurposeInput.required = true;

            // Show department for academic court requests
            if (this.value === 'Academic') {
                departmentContainer.style.display = 'block';
                departmentSelect.required = true;
            } else {
                departmentContainer.style.display = 'none';
                departmentSelect.required = false;
                departmentSelect.value = '';
            }
        } else {
            courtPurposeContainer.style.display = 'none';
            courtPurposeInput.required = false;
            courtPurposeInput.value = '';
            departmentContainer.style.display = 'none';
            departmentSelect.required = false;
            departmentSelect.value = '';
        }
    });

    // Court availability checking
    function checkCourtAvailability() {
        var eventDate = document.getElementById('event_date').value;
        var startTime = document.getElementById('start_time').value;
        var endTime = document.getElementById('end_time').value;
        var areaOfUse = document.getElementById('area_of_use').value;

        console.log('Checking court availability:', { eventDate, startTime, endTime, areaOfUse });

        if (!eventDate || !startTime || !endTime || areaOfUse !== 'Court') {
            console.log('Not all fields filled or not checking court, skipping check');
            return; // Don't check if fields are empty or not checking court
        }

        // Remove previous availability message
        var existingMsg = document.getElementById('court_availability_message');
        if (existingMsg) {
            existingMsg.remove();
        }

        // Show loading message
        var messageDiv = document.createElement('div');
        messageDiv.id = 'court_availability_message';
        messageDiv.className = 'mt-2 alert alert-info';
        messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking court availability...';

        var courtContainer = document.getElementById('court_type_container');
        if (courtContainer) {
            courtContainer.parentNode.insertBefore(messageDiv, courtContainer.nextSibling);
        }

        fetch('/api/check-court-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                event_date: eventDate,
                start_time: startTime,
                end_time: endTime
            })
        })
        .then(response => {
            console.log('Court response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Court response data:', data);
            var messageDiv = document.getElementById('court_availability_message');

            if (data.available) {
                messageDiv.className = 'mt-2 alert alert-success';
                messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Court is available for the selected time.';
            } else {
                messageDiv.className = 'mt-2 alert alert-danger';
                var conflicts = data.conflicting_events.map(event =>
                    `${event.title} (${event.start_time} - ${event.end_time}) by ${event.user}`
                ).join('<br>');
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Court is not available. Conflicts:<br>' + conflicts;
            }
        })
        .catch(error => {
            console.error('Error checking court availability:', error);
            var messageDiv = document.getElementById('court_availability_message');
            if (messageDiv) {
                messageDiv.className = 'mt-2 alert alert-warning';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error checking court availability. Please try again.';
            }
        });
    }

    // AVR availability checking
    function checkAvrAvailability() {
        var avrSelection = document.getElementById('avr_selection').value;
        var eventDate = document.getElementById('event_date').value;
        var startTime = document.getElementById('start_time').value;
        var endTime = document.getElementById('end_time').value;
        var areaOfUse = document.getElementById('area_of_use').value;

        console.log('Checking AVR availability:', { avrSelection, eventDate, startTime, endTime, areaOfUse });

        if (!avrSelection || !eventDate || !startTime || !endTime || areaOfUse !== 'AVR') {
            console.log('Not all fields filled or not checking AVR, skipping check');
            return; // Don't check if fields are empty or not checking AVR
        }

        // Remove previous availability message
        var existingMsg = document.getElementById('avr_availability_message');
        if (existingMsg) {
            existingMsg.remove();
        }

        // Show loading message
        var messageDiv = document.createElement('div');
        messageDiv.id = 'avr_availability_message';
        messageDiv.className = 'mt-2 alert alert-info';
        messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking AVR availability...';

        var avrContainer = document.getElementById('avr_request_category_container');
        if (avrContainer) {
            avrContainer.parentNode.insertBefore(messageDiv, avrContainer.nextSibling);
        }

        fetch('/api/check-avr-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                avr_selection: avrSelection,
                event_date: eventDate,
                start_time: startTime,
                end_time: endTime
            })
        })
        .then(response => {
            console.log('AVR response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('AVR response data:', data);
            var messageDiv = document.getElementById('avr_availability_message');

            if (data.available) {
                messageDiv.className = 'mt-2 alert alert-success';
                messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + avrSelection + ' is available for the selected time.';
            } else {
                messageDiv.className = 'mt-2 alert alert-danger';
                var conflicts = data.conflicting_events.map(event =>
                    `${event.title} (${event.start_time} - ${event.end_time}) by ${event.user}`
                ).join('<br>');
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + avrSelection + ' is not available. Conflicts:<br>' + conflicts;
            }
        })
        .catch(error => {
            console.error('Error checking AVR availability:', error);
            var messageDiv = document.getElementById('avr_availability_message');
            if (messageDiv) {
                messageDiv.className = 'mt-2 alert alert-warning';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error checking AVR availability. Please try again.';
            }
        });
    }

    // Room availability checking
    function checkRoomAvailability() {
        var roomNumber = document.getElementById('room_number') ? document.getElementById('room_number').value : '';
        var eventDate = document.getElementById('event_date').value;
        var startTime = document.getElementById('start_time').value;
        var endTime = document.getElementById('end_time').value;

        console.log('Checking availability:', { roomNumber, eventDate, startTime, endTime });

        if (!roomNumber || !eventDate || !startTime || !endTime) {
            console.log('Not all fields filled, skipping check');
            return; // Don't check if fields are empty
        }

        // Remove previous availability message
        var existingMsg = document.getElementById('availability-message');
        if (existingMsg) {
            existingMsg.remove();
        }

        // Show loading message
        var messageDiv = document.createElement('div');
        messageDiv.id = 'availability-message';
        messageDiv.className = 'mt-2 alert alert-info';
        messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking room availability...';

        var roomContainer = document.getElementById('room_number_container');
        if (roomContainer) {
            roomContainer.parentNode.insertBefore(messageDiv, roomContainer.nextSibling);
        }

        fetch('/api/check-room-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                room_number: roomNumber,
                event_date: eventDate,
                start_time: startTime,
                end_time: endTime
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            var messageDiv = document.getElementById('availability-message');

            if (data.available) {
                messageDiv.className = 'mt-2 alert alert-success';
                messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Room is available for the selected time.';
            } else {
                messageDiv.className = 'mt-2 alert alert-danger';
                var conflicts = data.conflicting_events.map(event =>
                    `${event.title} (${event.start_time} - ${event.end_time}) by ${event.user}`
                ).join('<br>');
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Room is not available. Conflicts:<br>' + conflicts;
            }
        })
        .catch(error => {
            console.error('Error checking room availability:', error);
            var messageDiv = document.getElementById('availability-message');
            if (messageDiv) {
                messageDiv.className = 'mt-2 alert alert-warning';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error checking availability. Please try again.';
            }
        });
    }

    // Add event listeners for availability checking
    function setupAvailabilityListeners() {
        var roomNumber = document.getElementById('room_number');
        var eventDate = document.getElementById('event_date');
        var startTime = document.getElementById('start_time');
        var endTime = document.getElementById('end_time');
        var areaOfUse = document.getElementById('area_of_use');

        // Room availability listeners
        if (roomNumber) roomNumber.addEventListener('change', checkRoomAvailability);
        if (eventDate) eventDate.addEventListener('change', checkRoomAvailability);
        if (startTime) startTime.addEventListener('change', checkRoomAvailability);
        if (endTime) endTime.addEventListener('change', checkRoomAvailability);

        // Also trigger on blur for better UX
        if (roomNumber) roomNumber.addEventListener('blur', checkRoomAvailability);
        if (eventDate) eventDate.addEventListener('blur', checkRoomAvailability);
        if (startTime) startTime.addEventListener('blur', checkRoomAvailability);
        if (endTime) endTime.addEventListener('blur', checkRoomAvailability);

        // Court availability listeners
        if (areaOfUse) areaOfUse.addEventListener('change', checkCourtAvailability);
        if (eventDate) eventDate.addEventListener('change', checkCourtAvailability);
        if (startTime) startTime.addEventListener('change', checkCourtAvailability);
        if (endTime) endTime.addEventListener('change', checkCourtAvailability);

        // Also trigger on blur for better UX
        if (areaOfUse) areaOfUse.addEventListener('blur', checkCourtAvailability);
        if (eventDate) eventDate.addEventListener('blur', checkCourtAvailability);
        if (startTime) startTime.addEventListener('blur', checkCourtAvailability);
        if (endTime) endTime.addEventListener('blur', checkCourtAvailability);

        // AVR availability listeners
        var avrSelection = document.getElementById('avr_selection');
        if (avrSelection) avrSelection.addEventListener('change', checkAvrAvailability);
        if (eventDate) eventDate.addEventListener('change', checkAvrAvailability);
        if (startTime) startTime.addEventListener('change', checkAvrAvailability);
        if (endTime) endTime.addEventListener('change', checkAvrAvailability);

        // Also trigger on blur for better UX
        if (avrSelection) avrSelection.addEventListener('blur', checkAvrAvailability);
        if (eventDate) eventDate.addEventListener('blur', checkAvrAvailability);
        if (startTime) startTime.addEventListener('blur', checkAvrAvailability);
        if (endTime) endTime.addEventListener('blur', checkAvrAvailability);
    }

    setupAvailabilityListeners();

    // Re-setup listeners when area of use changes (room_number field appears/disappears)
    document.getElementById('area_of_use').addEventListener('change', function() {
        setTimeout(setupAvailabilityListeners, 100); // Small delay to ensure DOM updates
    });

    // Form validation
    document.querySelector('form[action="{{ route('events.store') }}"]').addEventListener('submit', function(e) {
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        
        var errors = [];
        var isValid = true;
        
        // Title validation
        var title = document.getElementById('title');
        if (!title.value.trim()) {
            showFieldError(title, 'Event title is required');
            errors.push('Title is required');
            isValid = false;
        } else if (title.value.trim().length < 3) {
            showFieldError(title, 'Event title must be at least 3 characters');
            isValid = false;
        }
        
        // Category validation
        var category = document.getElementById('category');
        if (!category.value) {
            showFieldError(category, 'Please select a category');
            isValid = false;
        }

        // Area of Use validation (if Area Use is selected)
        var areaOfUse = document.getElementById('area_of_use');
        if (category.value === 'Area Use' && !areaOfUse.value) {
            showFieldError(areaOfUse, 'Please select an area of use');
            isValid = false;
        }

        // Court Type validation (if Court is selected in Area of Use)
        var courtType = document.getElementById('court_type');
        if (category.value === 'Area Use' && areaOfUse.value === 'Court' && !courtType.value) {
            showFieldError(courtType, 'Please select a court type');
            isValid = false;
        }

        // Room Number validation (if Room is selected in Area of Use)
        var roomNumber = document.getElementById('room_number');
        if (category.value === 'Area Use' && areaOfUse.value === 'Room' && !roomNumber.value) {
            showFieldError(roomNumber, 'Please select a room number');
            isValid = false;
        }

        // Department validation (if Room is selected in Area of Use or Academic Court is selected)
        var department = document.getElementById('department');
        if ((category.value === 'Area Use' && areaOfUse.value === 'Room' && !department.value) ||
            (category.value === 'Area Use' && areaOfUse.value === 'Court' && courtType.value === 'Academic' && !department.value)) {
            showFieldError(department, 'Please select a department');
            isValid = false;
        }

        // Court Purpose validation (if Court Type is selected)
        var courtPurpose = document.getElementById('court_purpose');
        if (category.value === 'Area Use' && areaOfUse.value === 'Court' && courtType.value && !courtPurpose.value.trim()) {
            showFieldError(courtPurpose, 'Please enter the purpose for court use');
            isValid = false;
        }

        // AVR Selection validation (if AVR is selected in Area of Use)
        var avrSelection = document.getElementById('avr_selection');
        if (category.value === 'Area Use' && areaOfUse.value === 'AVR' && !avrSelection.value) {
            showFieldError(avrSelection, 'Please select an AVR');
            isValid = false;
        }

        // AVR Request Category validation (if AVR Selection is selected)
        var avrRequestCategory = document.getElementById('avr_request_category');
        if (category.value === 'Area Use' && areaOfUse.value === 'AVR' && avrSelection.value && !avrRequestCategory.value) {
            showFieldError(avrRequestCategory, 'Please select a request category');
            isValid = false;
        }

        // Check room availability before submission if room is selected
        if (roomNumber.value) {
            // This is a simple synchronous check - in a real app, you'd want to make this async
            var availabilityMsg = document.getElementById('availability-message');
            if (availabilityMsg && availabilityMsg.classList.contains('alert-danger')) {
                alert('The selected room is not available for the chosen time. Please select a different time or room.');
                isValid = false;
            }
        }

        // Check court availability before submission if court is selected
        var areaOfUse = document.getElementById('area_of_use');
        if (areaOfUse.value === 'Court') {
            var courtAvailabilityMsg = document.getElementById('court_availability_message');
            if (courtAvailabilityMsg && courtAvailabilityMsg.classList.contains('alert-danger')) {
                alert('The court is not available for the chosen time. Please select a different time.');
                isValid = false;
            }
        }

        // Check AVR availability before submission if AVR is selected
        if (areaOfUse.value === 'AVR') {
            var avrAvailabilityMsg = document.getElementById('avr_availability_message');
            if (avrAvailabilityMsg && avrAvailabilityMsg.classList.contains('alert-danger')) {
                alert('The selected AVR is not available for the chosen time. Please select a different time or AVR.');
                isValid = false;
            }
        }
        
        // Date validation
        var eventDate = document.getElementById('event_date');
        if (!eventDate.value) {
            showFieldError(eventDate, 'Event date is required');
            isValid = false;
        } else {
            var selectedDate = new Date(eventDate.value);
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showFieldError(eventDate, 'Event date cannot be in the past');
                isValid = false;
            }
        }
        
        // Time validation
        var startTime = document.getElementById('start_time');
        var endTime = document.getElementById('end_time');
        if (!startTime.value) {
            showFieldError(startTime, 'Start time is required');
            isValid = false;
        }
        if (!endTime.value) {
            showFieldError(endTime, 'End time is required');
            isValid = false;
        }
        if (startTime.value && endTime.value && endTime.value <= startTime.value) {
            showFieldError(endTime, 'End time must be after start time');
            isValid = false;
        }

        // Description validation
        var description = document.getElementById('description');
        if (!description.value.trim()) {
            showFieldError(description, 'Description is required');
            isValid = false;
        } else if (description.value.trim().length < 10) {
            showFieldError(description, 'Description must be at least 10 characters');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            var firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        var feedbackDiv = field.nextElementSibling;
        if (!feedbackDiv || !feedbackDiv.classList.contains('invalid-feedback')) {
            var div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = message;
            field.parentNode.insertBefore(div, field.nextSibling);
        } else {
            feedbackDiv.textContent = message;
        }
    }

    // Materials/Equipment dynamic rows
    let materialRowCount = 1;

    function addMaterialRow() {
        const table = document.getElementById('materialsTable').getElementsByTagName('tbody')[0];
        const newRow = table.insertRow();
        newRow.innerHTML = `
            <td><input type="number" class="form-control" name="materials[${materialRowCount}][qty]" min="1" placeholder="1"></td>
            <td><input type="text" class="form-control" name="materials[${materialRowCount}][item]" placeholder="e.g., Projector, Chair, etc."></td>
            <td><input type="text" class="form-control" name="materials[${materialRowCount}][purpose]" placeholder="e.g., For presentation"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeMaterialRow(this)"><i class="fas fa-times"></i></button></td>
        `;
        materialRowCount++;
    }

    function removeMaterialRow(button) {
        const table = document.getElementById('materialsTable').getElementsByTagName('tbody')[0];
        if (table.rows.length > 1) {
            button.closest('tr').remove();
        }
    }
</script>
@endsection
@endsection
