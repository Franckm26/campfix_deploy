<div id="staffImportReview" style="display:none"
    data-preview-url="{{ route('admin.users.import.staff.preview') }}"
    data-confirm-url="{{ route('admin.users.import.staff.confirm') }}">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
        <h6 class="mb-0">Review staff users</h6>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="staffReviewBack">Choose another file</button>
    </div>
    <p class="text-muted">Edit the names, email addresses, and roles below. No accounts are created until you confirm.</p>
    <div id="staffReviewErrors" class="alert alert-danger" role="alert" hidden></div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Remove</th></tr></thead>
            <tbody id="staffReviewRows"></tbody>
        </table>
    </div>
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <span id="staffReviewCount" class="small text-muted" aria-live="polite"></span>
        <div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary" id="staffReviewPrevious">Previous</button><button type="button" class="btn btn-outline-secondary" id="staffReviewNext">Next</button></div>
    </div>
    <div class="text-end"><button type="submit" class="btn btn-primary" id="staffReviewConfirm">Confirm and Import Staff</button></div>
</div>
