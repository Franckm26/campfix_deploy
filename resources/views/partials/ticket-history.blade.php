<section class="mt-4 pt-3 border-top text-start" aria-label="Repair and reopening history" style="overflow-wrap:anywhere;white-space:normal;min-width:0">
    <h5 class="mb-3">History</h5>
    @forelse($history as $record)
        <article class="border rounded p-3 mb-3">
            <h6>{{ $record['label'] }}</h6>
            @if($record['legacy'])
                <p class="small text-muted">Original ticket details were not recorded for this older entry. Current ticket details are shown for reference; the saved repair details appear below.</p>
            @endif
            <div class="row g-3 mx-0">
                <div class="col-12 col-md-6 px-0 pe-md-3">
                    @foreach([
                        'Ticket reference' => $record['reference'], 'Title / issue' => $record['title'],
                        'Category' => $record['category_name'], 'Location' => $record['location'],
                        'Location type' => $record['location_type'], 'Room number' => $record['room_number'],
                        'Status' => $record['status'], 'Priority' => $record['priority'],
                        'Safety hazard' => $record['is_safety_hazard'] ? 'Yes' : 'No',
                        'Report count' => $record['report_count'],
                    ] as $label => $value)
                        <p class="mb-1"><strong>{{ $label }}:</strong> {{ $value ?? 'Not recorded' }}</p>
                    @endforeach
                </div>
                <div class="col-12 col-md-6 px-0">
                    <p class="mb-1"><strong>Reported by:</strong> {{ $record['reported_by'] }}</p>
                    @foreach($record['reporter_contact'] as $label => $value)
                        <p class="mb-1"><strong>{{ $label }}:</strong> {{ $value }}</p>
                    @endforeach
                    @foreach([
                        'Date submitted' => $record['created_at'], 'Assigned to' => $record['assigned_to'],
                        'Date assigned' => $record['assigned_at'], 'Work started' => $record['in_progress_at'],
                        'Date resolved' => $record['resolved_at'],
                    ] as $label => $value)
                        <p class="mb-1"><strong>{{ $label }}:</strong> {{ $value ?? 'Not recorded' }}</p>
                    @endforeach
                </div>
            </div>
            @foreach([
                'Description' => $record['description'], 'Additional details' => $record['details'],
                'Resolution notes' => $record['resolution_notes'],
                'Damaged part' => $record['damaged_part'], 'Replaced part' => $record['replaced_part'],
            ] as $label => $value)
                <p class="mt-2 mb-1"><strong>{{ $label }}:</strong></p>
                <p class="mb-1" style="white-space:pre-wrap">{{ $value ?: 'Not recorded' }}</p>
            @endforeach
            <p class="mt-2 mb-1"><strong>Recorded cost:</strong> {{ $record['cost'] !== null ? 'PHP '.number_format((float) $record['cost'], 2) : 'Not recorded' }}</p>
            <p class="mb-1"><strong>Budget status:</strong> {{ ucfirst($record['budget_status'] ?? 'Not requested') }}</p>
            @if($record['budget_status'] || $record['budget_amount'] !== null)
                <p class="mb-1"><strong>Requested budget:</strong> {{ $record['budget_amount'] !== null ? 'PHP '.number_format((float) $record['budget_amount'], 2) : 'Not recorded' }}</p>
                @foreach([
                    'Budget requested by' => $record['budget_requested_by_name'], 'Budget requested on' => $record['budget_requested_at'],
                    'Budget reviewed by' => $record['budget_reviewed_by_name'], 'Budget reviewed on' => $record['budget_reviewed_at'],
                    'Budget rejection reason' => $record['budget_rejection_reason'],
                ] as $label => $value)
                    <p class="mb-1"><strong>{{ $label }}:</strong> {{ $value ?? 'Not recorded' }}</p>
                @endforeach
            @endif
            @if($record['photo_url'])
                <div class="mt-3">
                    <p class="mb-1"><strong>Photo:</strong></p>
                    <a href="{{ $record['photo_url'] }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $record['photo_url'] }}" alt="Ticket attachment" loading="lazy" class="img-fluid rounded" style="max-height:300px;max-width:100%;object-fit:contain">
                    </a>
                </div>
            @else
                <p class="mb-1"><strong>Photo:</strong> Not provided</p>
            @endif
            @if($record['reopened_at'])
                <div class="border-top mt-3 pt-2">
                    <p class="mb-1"><strong>Reopened:</strong> {{ $record['reopened_at'] }}</p>
                    <p class="mb-1"><strong>Reopened by:</strong> {{ $record['reopened_by'] }}</p>
                    <p class="mb-1"><strong>Reason for reopening:</strong></p>
                    <p class="mb-0" style="white-space:pre-wrap">{{ $record['reason'] }}</p>
                </div>
            @endif
        </article>
    @empty
        <p class="text-muted">No previous repair or reopening records for this ticket.</p>
    @endforelse
</section>
