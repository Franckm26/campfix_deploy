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
                        <div class="analytics-summary-metric"><span>Workload Share</span><strong>{{ number_format($dominantStatusShare, 1) }}%</strong></div>
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

<div class="modal fade" id="categorySummaryModal" tabindex="-1" aria-labelledby="categorySummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div><h3 class="modal-title fs-5" id="categorySummaryLabel"><i class="fas fa-chart-column text-primary"></i> Category Analysis Executive Summary</h3><small class="text-muted">{{ $executiveSummary['period'] }}</small></div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body executive-summary" id="categorySummaryContent">
                @if($topCategory)
                    <div class="analytics-summary-metrics">
                        <div class="analytics-summary-metric"><span>Leading Category</span><strong>{{ $topCategory['category'] }}</strong></div>
                        <div class="analytics-summary-metric"><span>Reports</span><strong>{{ number_format($topCategory['count']) }}</strong></div>
                        <div class="analytics-summary-metric"><span>Workload Share</span><strong>{{ number_format($topCategoryShare, 1) }}%</strong></div>
                    </div>
                    <p><strong>{{ $topCategory['category'] }}</strong> accounts for {{ number_format($topCategoryShare, 1) }}% of all reports in the selected period. This concentration indicates where preventive maintenance, technical training, spare parts, and supplier planning may have the greatest effect.</p>
                    <div class="summary-callout"><strong>Decision:</strong> Review the recurring causes within {{ $topCategory['category'] }}, compare required parts against available inventory, and schedule preventive work at the locations contributing most to this category.</div>
                @else
                    <p>No category information is available for the selected filters.</p>
                @endif
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="button" data-print-summary="categorySummaryContent" data-title="Category Analysis Executive Summary"><i class="fas fa-print"></i> Print Summary</button></div>
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
