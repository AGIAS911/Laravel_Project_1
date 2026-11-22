@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        @if ($paginator->previousPageUrl())
            <a class="btn btn-outline btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif
        <span class="muted sm">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @if ($paginator->nextPageUrl())
            <a class="btn btn-outline btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @endif
    </nav>
@endif
