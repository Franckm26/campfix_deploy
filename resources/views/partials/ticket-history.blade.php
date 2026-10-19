<section class="mt-4 pt-3 border-top text-start" aria-label="Repair and reopening history" style="overflow-wrap:anywhere;white-space:normal;min-width:0">
    <h5 class="mb-3">Past records</h5>
    @forelse($history as $record)
        <article class="border rounded p-3 mb-3">
            <h6>{{ $record['label'] }}</h6>
            <p class="mb-1"><strong>Assigned to:</strong> {{ $record['assigned_to'] }}</p>
            @if($record['assigned_at'])
                <p class="mb-1"><strong>Date assigned:</strong> {{ $record['assigned_at'] }}</p>
            @endif
            <p class="mb-1"><strong>Date resolved:</strong> {{ $record['resolved_at'] ?? 'Not recorded' }}</p>
            <p class="mb-1"><strong>Resolution notes:</strong></p>
            <p style="white-space:pre-wrap">{{ $record['resolution_notes'] ?: 'No resolution notes recorded.' }}</p>
            @if($record['cost'] !== null)
                <p class="mb-1"><strong>Recorded cost:</strong> PHP {{ number_format((float) $record['cost'], 2) }}</p>
            @endif
            @if($record['damaged_part'])
                <p class="mb-1"><strong>Damaged part:</strong> {{ $record['damaged_part'] }}</p>
            @endif
            @if($record['replaced_part'])
                <p class="mb-1"><strong>Replaced part:</strong> {{ $record['replaced_part'] }}</p>
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
