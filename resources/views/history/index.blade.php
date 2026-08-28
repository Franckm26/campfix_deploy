@extends('layouts.app')

@section('page_title')
<h2>History</h2>
<p>Your personal account and security activity</p>
@endsection

@push('styles')
<style>
    /* Make pagination arrows smaller */
    .pagination-sm-arrows .pagination {
        font-size: 0.875rem;
    }
    
    .pagination-sm-arrows .page-link {
        padding: 0.375rem 0.75rem;
        line-height: 1.25;
    }
    
    .pagination-sm-arrows .page-link svg,
    .pagination-sm-arrows .page-link i {
        font-size: 0.75rem;
        width: 0.875rem;
        height: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="mb-3">
                <h4 class="mb-1"><i class="fas fa-history text-primary me-2"></i>My History</h4>
                <p class="text-muted mb-0">Only activity performed by your account is shown here. This page is read-only.</p>
            </div>

            <form method="GET" action="{{ route('history.index') }}" class="row g-2 align-items-end mb-4">
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="historySearch">Search</label>
                    <input id="historySearch" type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search action or description...">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="historyDateFrom">From</label>
                    <input id="historyDateFrom" type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="historyDateTo">To</label>
                    <input id="historyDateTo" type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="historyPerPage">Show</label>
                    <select id="historyPerPage" name="per_page" class="form-select">
                        @foreach([10, 20, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} per page</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a class="btn btn-secondary" href="{{ route('history.index') }}" aria-label="Clear filters"><i class="fas fa-times"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 180px;">Action</th>
                            <th scope="col">Details</th>
                            <th scope="col" style="width: 130px;">IP Address</th>
                            <th scope="col" style="width: 150px;">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $entry)
                            <tr>
                                <td class="fw-semibold text-nowrap">{{ str($entry->action)->replace('_', ' ')->title() }}</td>
                                <td style="min-width:280px; max-width: 500px; word-wrap: break-word;">{{ $entry->description ?: 'No additional details recorded.' }}</td>
                                <td class="text-nowrap">{{ $entry->ip_address ?: 'N/A' }}</td>
                                <td class="text-nowrap">
                                    {{ optional($entry->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}<br>
                                    <span class="text-muted">{{ optional($entry->created_at)->timezone('Asia/Manila')->format('g:i:s A') }} PHT</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                    No activity matches the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($history->hasPages())
                <div class="mt-3 pagination-sm-arrows">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
