@php
    $reopenOwnerId = $ticket instanceof \App\Models\Report && $ticket->concern_id
        ? $ticket->concern?->user_id : $ticket->user_id;
@endphp
@if((int) $reopenOwnerId === (int) auth()->id() && $ticket->status === 'Resolved' && !$ticket->is_deleted && !$ticket->deleted_at)
    <details class="my-2 text-start">
        <summary class="text-primary">Reopen {{ $ticket instanceof \App\Models\Concern ? 'concern' : 'report' }}</summary>
        <form method="POST" action="{{ route($ticket instanceof \App\Models\Concern ? 'concerns.reopen' : 'reports.reopen', $ticket->id) }}" class="mt-2">
            @csrf
            <p class="small text-muted">Reopen this ticket if the same problem remains or has returned.</p>
            <label class="form-label" for="reopen-reason-{{ $ticket->getTable() }}-{{ $ticket->id }}-{{ $context ?? 'detail' }}">Reason for reopening</label>
            <textarea id="reopen-reason-{{ $ticket->getTable() }}-{{ $ticket->id }}-{{ $context ?? 'detail' }}" name="reason" class="form-control mb-2" rows="2" maxlength="1000" required>{{ old('reason') }}</textarea>
            @error('reason')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn btn-sm btn-warning">Reopen ticket</button>
        </form>
    </details>
@endif
