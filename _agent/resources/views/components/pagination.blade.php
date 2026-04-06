@if ($paginator->hasPages())
<nav class="pagination" role="navigation" aria-label="Pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="pagination-prev" style="opacity:0.3;" aria-disabled="true">&lsaquo;</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="pagination-prev" rel="prev">&lsaquo;</a>
    @endif

    {{-- Page numbers --}}
    <div class="pagination-pages">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-dots">&hellip;</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="pagination-next" rel="next">&rsaquo;</a>
    @else
        <span class="pagination-next" style="opacity:0.3;" aria-disabled="true">&rsaquo;</span>
    @endif
</nav>
@endif
