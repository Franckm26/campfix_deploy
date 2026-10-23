<section class="border-top mt-4 pt-3 text-start">
    <h5>Asset replacement</h5>
    @if($canRecordReplacement)
        <p class="small text-muted">Record a complete asset replacement after resolving the work. The new asset starts a fresh recurrence count for this issue and location. Previous records remain in History.</p>
        <button type="button" class="btn btn-outline-primary btn-sm" data-record-asset-replacement
            data-url="{{ route('tickets.assetReplacement', ['resource' => $ticket instanceof \App\Models\Report ? 'reports' : 'concerns', 'id' => $ticket->id]) }}"
            data-operation-id="{{ \Illuminate\Support\Str::uuid() }}" data-issue="{{ $ticket->title }}" data-location="{{ $ticket->location }}">
            <i class="fas fa-rotate me-1"></i> Record asset replacement
        </button>
    @endif
    @forelse($replacements as $replacement)
        <article class="border rounded p-3 mt-3">
            <strong>Asset replaced — new repair cycle started</strong>
            <p class="mb-1 mt-2"><strong>New asset:</strong> {{ $replacement->metadata['replacement_details'] }}</p>
            <p class="mb-1"><strong>Recorded:</strong> {{ $replacement->created_at->copy()->timezone('Asia/Manila')->format('M d, Y h:i A') }}</p>
            <p class="mb-1"><strong>Recorded by:</strong> {{ $replacement->user?->name ?? 'Unavailable user' }}</p>
            <p class="mb-0" style="white-space:pre-wrap"><strong>Notes:</strong> {{ $replacement->metadata['replacement_notes'] }}</p>
        </article>
    @empty
        <p class="text-muted small mt-2">No asset replacement has been recorded for this issue and location.</p>
    @endforelse
</section>
