<div class="mb-3" id="facility-room-selection">
    <input type="hidden" name="rooms[0][location]" id="first-room-location">
    <label for="first-room-reason" class="form-label">Reason for using this room *</label>
    <textarea id="first-room-reason" name="rooms[0][reason]" class="form-control" rows="2" maxlength="500" required placeholder="Explain why you need this room">{{ old('rooms.0.reason') }}</textarea>
    <div id="first-room-availability" class="small mt-2" role="status"></div>
    <div id="additional-facility-rooms"></div>
    <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="add-facility-room"><i class="fas fa-plus me-1"></i> Add another room</button>
    <div class="form-text">Select up to 10 rooms. All selected rooms use the same date, time, and approval.</div>
</div>
