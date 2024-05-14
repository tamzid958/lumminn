@if ($paginator->hasPages())
    <div class="join grid grid-cols-2 w-80 mx-auto">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <a class="join-item btn btn-outline" disabled> {!! __('pagination.previous') !!}</a>
        @else
            <a class="btn btn-outline join-item"
               href={{ $paginator->previousPageUrl()}}> {!! __('pagination.previous') !!}</a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="btn btn-outline join-item"
               href="{{ $paginator->nextPageUrl()}}"> {!! __('pagination.next') !!}</a>
        @else
            <a class="join-item btn btn-outline" disabled> {!! __('pagination.next') !!}</a>
        @endif
    </div>
@endif
