@extends('layouts.app')

@section('page_title')
<h2>History</h2>
<p>Your personal account and security activity</p>
@endsection

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
                <table class="table table-hover mb-0" style="font-size:13px">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:14%">Action</th>
                            <th>Details</th>
                            <th style="width:12%">IP Address</th>
                            <th style="width:14%">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $entry)
                            <tr>
                                <td style="font-size:13px; color:#374151; font-weight:500;">
                                    {{ str($entry->action)->replace('_', ' ')->title() }}
                                </td>
                                <td>
                                    <div style="font-size:13px; line-height:1.7;">
                                        {{ $entry->description ?: 'No additional details recorded.' }}
                                    </div>
                                </td>
                                <td class="text-muted font-monospace" style="font-size:11px">
                                    {{ $entry->ip_address ?: 'N/A' }}
                                </td>
                                <td class="text-muted">
                                    {{ optional($entry->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}<br>
                                    <small>{{ optional($entry->created_at)->timezone('Asia/Manila')->format('g:i:s A') }} PHT</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                    No activity matches the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                <small class="text-muted">Showing {{ $history->firstItem() ?? 0 }} – {{ $history->lastItem() ?? 0 }} of {{ $history->total() }} entries</small>
                {{ $history->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
