<div class="modal fade" id="alertEvidenceModal" tabindex="-1" aria-labelledby="alertEvidenceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div><h3 class="modal-title fs-5" id="alertEvidenceLabel"><i class="fas fa-triangle-exclamation text-danger"></i> <span id="alertEvidenceTitle">Decision Alert</span></h3><small class="text-muted">Evidence, impact, and recommended response</small></div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="location-risk-callout" id="alertEvidenceCallout"><strong>Why this alert appeared:</strong> <span id="alertEvidenceWhy"></span></div>
                <div class="analytics-summary-metrics" id="alertEvidenceStats"></div>
                <div class="analytics-grid-two">
                    <section class="location-breakdown"><h4>Trend Evidence</h4><div style="height:190px"><canvas id="alertEvidenceChart"></canvas></div></section>
                    <section class="location-breakdown"><h4>Suggested Actions</h4><ol class="dss-action-list" id="alertEvidenceActions"></ol></section>
                </div>
                <div class="summary-callout"><strong>Estimated impact:</strong> <span id="alertEvidenceImpact"></span><br><strong>Priority:</strong> <span id="alertEvidencePriority"></span></div>
                <section class="location-reports"><h4>Related Reports and Assigned Personnel</h4><table class="location-report-table"><thead><tr><th>Report</th><th>Location</th><th>Status</th><th>Priority</th><th>Age</th><th>Assigned To</th></tr></thead><tbody id="alertEvidenceReports"></tbody></table></section>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><a class="btn btn-primary" href="{{ route('admin.reports') }}"><i class="fas fa-list"></i> Open Reports</a></div>
        </div>
    </div>
</div>
