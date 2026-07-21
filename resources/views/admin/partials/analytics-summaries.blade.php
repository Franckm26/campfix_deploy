<div class="modal fade" id="statusSummaryModal" tabindex="-1" aria-labelledby="statusSummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div><h3 class="modal-title fs-5" id="statusSummaryLabel"><i class="fas fa-chart-pie text-primary"></i> Status Distribution Executive Summary</h3><small class="text-muted">{{ $executiveSummary['period'] }}</small></div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body executive-summary" id="statusSummaryContent">
                @if($dominantStatus)
                    <div class="analytics-summary-metrics">
                        <div class="analytics-summary-metric"><span>Dominant Status</span><strong>{{ $dominantStatus['status'] }}</strong></div>
                        <div class="analytics-summary-metric"><span>Average Age</span><strong>{{ number_format($dominantStatus['avg_age_days'], 1) }} days</strong></div>
                        <div class="analytics-summary-metric"><span>Open Workload</span><strong>{{ number_format($openReports) }}</strong></div>
                    </div>
                    <p><strong>{{ $dominantStatus['status'] }}</strong> is the largest status group with {{ number_format($dominantStatus['count']) }} of {{ number_format($totalReports) }} reports. The overall resolution rate is {{ number_format($resolutionRate, 1) }}%, compared with the {{ number_format($targetResolutionRate) }}% target.</p>
                    <div class="summary-callout"><strong>Decision:</strong> @if($resolutionRate < $targetResolutionRate) Review pending and assigned reports by age, confirm ownership, and escalate stalled work until the backlog returns to target. @else Status distribution is within target; continue monitoring assigned work so it does not become an ageing backlog. @endif</div>
                @else
                    <p>No status information is available for the selected filters.</p>
                @endif
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="button" data-print-summary="statusSummaryContent" data-title="Status Distribution Executive Summary"><i class="fas fa-print"></i> Print Summary</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="locationDetailModal" tabindex="-1" aria-labelledby="locationDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title fs-5" id="locationDetailLabel"><i class="fas fa-location-dot text-primary"></i> <span id="locationDetailName">Location Details</span></h3>
                    <small class="text-muted">Report workload, risk factors, and recent activity</small>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="location-detail-metrics">
                    <div class="location-detail-metric"><span>Total Reports</span><strong id="locationDetailTotal">0</strong></div>
                    <div class="location-detail-metric"><span>Open</span><strong id="locationDetailOpen">0</strong></div>
                    <div class="location-detail-metric"><span>Resolved</span><strong id="locationDetailResolved">0</strong></div>
                    <div class="location-detail-metric"><span>Safety Hazards</span><strong id="locationDetailHazards">0</strong></div>
                    <div class="location-detail-metric"><span>Risk Score</span><strong id="locationDetailRisk">0</strong></div>
                </div>

                <div class="location-risk-callout" id="locationDetailCallout">
                    <strong>System interpretation:</strong> <span id="locationDetailInterpretation"></span>
                </div>

                <div class="location-breakdowns">
                    <section class="location-breakdown"><h4>Status Breakdown</h4><div id="locationStatusBreakdown"></div></section>
                    <section class="location-breakdown"><h4>Category Breakdown</h4><div id="locationCategoryBreakdown"></div></section>
                </div>

                <div class="analytics-summary-metrics">
                    <div class="analytics-summary-metric"><span>Resolution Rate</span><strong id="locationDetailRate">0%</strong></div>
                    <div class="analytics-summary-metric"><span>Recorded Cost</span><strong id="locationDetailCost">PHP 0.00</strong></div>
                    <div class="analytics-summary-metric"><span>Recommended Priority</span><strong id="locationDetailPriority">Low</strong></div>
                </div>

                <section class="location-reports">
                    <h4>Recent Reports</h4>
                    <table class="location-report-table">
                        <thead><tr><th>Date</th><th>Report</th><th>Category</th><th>Status</th><th>Priority</th><th>Hazard</th><th>Cost</th></tr></thead>
                        <tbody id="locationRecentReports"></tbody>
                    </table>
                </section>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <a class="btn btn-primary" id="locationDetailReportsLink" href="{{ route('admin.reports') }}"><i class="fas fa-list"></i> View Open Reports</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="categorySummaryModal" tabindex="-1" aria-labelledby="categorySummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div><h3 class="modal-title fs-5" id="categorySummaryLabel"><i class="fas fa-chart-column text-primary"></i> <span id="categorySummaryTitle">Category Analysis Executive Summary</span></h3><small class="text-muted">{{ $executiveSummary['period'] }}</small></div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body executive-summary" id="categorySummaryContent">
                <div class="analytics-summary-metrics category-summary-metrics" id="categorySummaryMetrics"></div>
                <p id="categorySummaryInterpretation"></p>
                <div class="summary-callout" id="categorySummaryDecision"></div>
                <section class="category-summary-section">
                    <h4 id="categorySummaryGraphTitle">Six-Month Category Trend</h4>
                    <img class="category-summary-chart" id="categorySummaryChartImage" alt="Selected category trend chart">
                </section>
                <section class="category-summary-section">
                    <div class="category-summary-section-heading"><h4>Ticket Evidence</h4><span id="categorySummaryTicketCount"></span></div>
                    <div class="category-summary-table-wrap">
                        <table class="category-summary-table">
                            <thead><tr><th>Ticket</th><th>Title</th><th>Location</th><th>Status</th><th>Assignee</th><th>Hazard</th><th>Cost</th></tr></thead>
                            <tbody id="categorySummaryTickets"></tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="button" data-print-summary="categorySummaryContent" data-dynamic-title="categorySummaryTitle" data-title="Category Analysis Executive Summary"><i class="fas fa-print"></i> Print Summary</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="locationSummaryModal" tabindex="-1" aria-labelledby="locationSummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div><h3 class="modal-title fs-5" id="locationSummaryLabel"><i class="fas fa-location-dot text-primary"></i> Location Risk Executive Summary</h3><small class="text-muted">{{ $executiveSummary['period'] }}</small></div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body executive-summary" id="locationSummaryContent">
                @if($topLocation)
                    <div class="analytics-summary-metrics">
                        <div class="analytics-summary-metric"><span>Highest Priority</span><strong>{{ $topLocation['location'] }}</strong></div>
                        <div class="analytics-summary-metric"><span>High-Risk Locations</span><strong>{{ $highRiskLocations->count() }}</strong></div>
                        <div class="analytics-summary-metric"><span>Open Location Work</span><strong>{{ number_format($locationOpenWork) }}</strong></div>
                    </div>
                    <p><strong>{{ $topLocation['location'] }}</strong> ranks first with a risk score of {{ $topLocation['risk_score'] }}, {{ $topLocation['open'] }} open report(s), and {{ $topLocation['hazards'] }} safety hazard(s). The ranking currently identifies {{ $highRiskLocations->count() }} high-risk and {{ $mediumRiskLocations->count() }} medium-risk location(s).</p>
                    <div class="summary-callout"><strong>Decision:</strong> Inspect {{ $topLocation['location'] }} first, assign hazard-related work before routine repairs, and use the exported ranking when allocating staff and repair funds across locations.</div>
                @else
                    <p>No location information is available for the selected filters.</p>
                @endif
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="button" data-print-summary="locationSummaryContent" data-title="Location Risk Executive Summary"><i class="fas fa-print"></i> Print Summary</button></div>
        </div>
    </div>
</div>
