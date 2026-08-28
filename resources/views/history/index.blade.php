@extends('layouts.app')

@section('page_title')
<h2>History</h2>
<p>Your personal account and security activity</p>
@endsection

@section('styles')
<style>
    .history-table-wrap {
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        min-width: 860px;
        table-layout: fixed;
    }

    .history-table th,
    .history-table td {
        padding: .9rem 1rem !important;
        vertical-align: top;
        text-align: left;
    }

    .history-table th {
        white-space: nowrap;
    }

    .history-table .history-action,
    .history-table .history-description {
        overflow-wrap: anywhere;
        word-break: normal;
    }

    .history-table .history-ip,
    .history-table .history-date {
        white-space: nowrap;
    }

    .history-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .history-pagination-summary {
        color: #6c757d;
        font-size: .9rem;
    }

    .history-page-list {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .3rem;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .history-page-link,
    .history-page-ellipsis {
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 .75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        border-radius: .35rem;
        background: #fff;
        color: #0d6efd;
        font-size: .9rem;
        line-height: 1;
        text-decoration: none;
    }

    .history-page-link:hover {
        background: #eef5ff;
        color: #0a58ca;
    }

    .history-page-link.is-current {
        border-color: #0d6efd;
        background: #0d6efd;
        color: #fff;
        font-weight: 600;
    }

    .history-page-link.is-disabled,
    .history-page-ellipsis {
        color: #8a939b;
        background: #f8f9fa;
        cursor: default;
    }

    @media (max-width: 767.98px) {
        .history-pagination {
            justify-content: center;
        }

        .history-pagination-summary {
            flex-basis: 100%;
            text-align: center;
        }
    }
</style>
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
                                <option value="10"  {{ request('per_page') == '10' ? 'selected' : '' }}>10 / page</option>
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

            <div class="history-table-wrap">
                <table class="table table-hover align-middle mb-0 history-table">
                    <colgroup>
                        <col style="width: 18%">
                        <col style="width: 52%">
                        <col style="width: 13%">
                        <col style="width: 17%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Action</th>
                            <th scope="col">Description</th>
                            <th scope="col">IP Address</th>
                            <th scope="col">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $entry)
                            <tr>
                                <td class="history-action fw-semibold">{{ str($entry->action)->replace('_', ' ')->title() }}</td>
                                <td class="history-description">{{ $entry->description ?: 'No additional details recorded.' }}</td>
                                <td class="history-ip">{{ $entry->ip_address ?: 'N/A' }}</td>
                                <td class="history-date">
                                    {{ optional($entry->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}<br>
                                    <span class="text-muted">{{ optional($entry->created_at)->timezone('Asia/Manila')->format('g:i:s A') }} PHT</span>
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
                @php
                    $currentPage = $history->currentPage();
                    $lastPage = $history->lastPage();
                    $pageStart = max(1, $currentPage - 2);
                    $pageEnd = min($lastPage, $currentPage + 2);
                @endphp
                <div class="history-pagination">
                    <div class="history-pagination-summary">
                        Showing {{ $history->firstItem() }}–{{ $history->lastItem() }} of {{ $history->total() }} activities
                    </div>
                    <nav aria-label="History pages">
                        <ul class="history-page-list">
                            <li>
                                @if($history->onFirstPage())
                                    <span class="history-page-link is-disabled" aria-disabled="true">Previous</span>
                                @else
                                    <a class="history-page-link" href="{{ $history->previousPageUrl() }}" rel="prev">Previous</a>
                                @endif
                            </li>

                            @if($pageStart > 1)
                                <li><a class="history-page-link" href="{{ $history->url(1) }}">1</a></li>
                                @if($pageStart > 2)
                                    <li><span class="history-page-ellipsis">&hellip;</span></li>
                                @endif
                            @endif

                            @for($page = $pageStart; $page <= $pageEnd; $page++)
                                <li>
                                    @if($page === $currentPage)
                                        <span class="history-page-link is-current" aria-current="page">{{ $page }}</span>
                                    @else
                                        <a class="history-page-link" href="{{ $history->url($page) }}">{{ $page }}</a>
                                    @endif
                                </li>
                            @endfor

                            @if($pageEnd < $lastPage)
                                @if($pageEnd < $lastPage - 1)
                                    <li><span class="history-page-ellipsis">&hellip;</span></li>
                                @endif
                                <li><a class="history-page-link" href="{{ $history->url($lastPage) }}">{{ $lastPage }}</a></li>
                            @endif

                            <li>
                                @if($history->hasMorePages())
                                    <a class="history-page-link" href="{{ $history->nextPageUrl() }}" rel="next">Next</a>
                                @else
                                    <span class="history-page-link is-disabled" aria-disabled="true">Next</span>
                                @endif
                            </li>
                        </ul>
                    </nav>
                </div>
            @elseif($history->total() > 0)
                <div class="history-pagination-summary mt-3">
                    Showing {{ $history->firstItem() }}–{{ $history->lastItem() }} of {{ $history->total() }} activities
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
