@if ($paginator->hasPages())
<nav style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem; margin-top:1rem;">

    {{-- Info --}}
    <div class="text-hell text-klein">
        {{ __('Showing') }} {{ $paginator->firstItem() }} {{ __('to') }} {{ $paginator->lastItem() }} {{ __('of') }} {{ $paginator->total() }} {{ __('results') }}
    </div>

    {{-- Seitennavigation --}}
    <div style="display:flex; gap:0.25rem; align-items:center; flex-wrap:wrap;">

        {{-- Zurück --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-sekundaer" style="opacity:0.4; cursor:default;">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sekundaer">&laquo;</a>
        @endif

        {{-- Seitenzahlen --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="btn btn-sekundaer" style="opacity:0.4; cursor:default;">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-primaer" style="cursor:default;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-sekundaer">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Weiter --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sekundaer">&raquo;</a>
        @else
            <span class="btn btn-sekundaer" style="opacity:0.4; cursor:default;">&raquo;</span>
        @endif

    </div>
</nav>
@endif
