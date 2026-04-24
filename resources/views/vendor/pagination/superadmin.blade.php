@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;margin-top:16px">
    <div style="font-size:12px;color:var(--sa-muted)">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </div>
    <div style="display:flex;gap:4px">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.04);color:var(--sa-muted);cursor:not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.06);color:var(--sa-text);text-decoration:none;border:1px solid var(--sa-border)" onmouseover="this.style.background='var(--sa-hover)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:5px 10px;border-radius:6px;font-size:12px;color:var(--sa-muted)">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="padding:5px 10px;border-radius:6px;font-size:12px;background:var(--sa-accent);color:#fff;font-weight:600">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.06);color:var(--sa-text);text-decoration:none;border:1px solid var(--sa-border)" onmouseover="this.style.background='var(--sa-hover)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.06);color:var(--sa-text);text-decoration:none;border:1px solid var(--sa-border)" onmouseover="this.style.background='var(--sa-hover)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <span style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.04);color:var(--sa-muted);cursor:not-allowed">
                <i class="fas fa-chevron-right"></i>
            </span>
        @endif
    </div>
</nav>
@endif
