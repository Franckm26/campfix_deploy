<div class="border rounded p-3 mb-3">
    <label class="form-check-label" for="swal-asset-replaced">
        <input class="form-check-input me-2" type="checkbox" id="swal-asset-replaced">
        A complete asset was replaced, not just a repaired part.
    </label>
    <div id="swal-asset-replacement-fields" class="mt-3" hidden>
        <p class="small text-muted">The new asset starts a fresh recurring-issue count. Previous repairs stay in History.</p>
        <label class="form-label fw-bold" for="swal-new-asset-details">New asset details *</label>
        <input id="swal-new-asset-details" class="form-control" maxlength="255" placeholder="Model, serial number or asset tag">
        <label class="form-label fw-bold mt-3" for="swal-asset-replacement-notes">Replacement notes *</label>
        <textarea id="swal-asset-replacement-notes" class="form-control" rows="3" maxlength="1000" placeholder="Describe the complete replacement and installation."></textarea>
    </div>
</div>
