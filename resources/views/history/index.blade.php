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

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('history.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search action / description" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-auto">
                            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:110px;padding-right:2rem">
                                <option value="20"  {{ (!request('per_page') || request('per_page') == '20') ? 'selected' : '' }}>20 / page</option>
                                <option value="50"  {{ request('per_page') == '50' ? 'selected' : '' }}>50 / page</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 / page</option>
                            </select>
                        </div>
                        <div class="col-auto d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('history.index') }}" class="btn btn-secondary btn-sm">X</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date / Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $entry)
                            <tr>
                                <td>{{ str($entry->action)->replace('_', ' ')->title() }}</td>
                                <td>{{ $entry->description ?: 'No additional details recorded.' }}</td>
                                <td>{{ $entry->ip_address ?: 'N/A' }}</td>
                                <td>
                                    {{ optional($entry->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}<br>
                                    {{ optional($entry->created_at)->timezone('Asia/Manila')->format('g:i:s A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No activity matches the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($history->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $history->appends(request()->except('page'))->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
