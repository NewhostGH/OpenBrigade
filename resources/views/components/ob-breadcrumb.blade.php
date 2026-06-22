{{--
    ob-breadcrumb  —  Page path navigation.

    Always prepends the Dashboard home icon automatically.
    The last item is rendered as plain text (current page, no link).

    Props
    ─────
    items   array   Ordered list of breadcrumb segments:
                    [
                      ['label' => 'Personnel',  'url' => route('personnel.index')],
                      ['label' => 'Jean Dupont'],   // last item → no url needed
                    ]
                    Each item may also have an optional 'icon' key (FA class).
--}}

@props([
    'items' => [],
])

<nav class="ob-breadcrumb noprint" aria-label="{{ __('components.breadcrumb_nav_label') }}">

    {{-- Home / Dashboard --}}
    <span class="ob-breadcrumb-item">
        <a href="{{ route('dashboard') }}" title="{{ __('components.breadcrumb_home_title') }}">
            <i class="fas fa-home"></i>
        </a>
    </span>

    @foreach ($items as $item)
        <span class="ob-breadcrumb-sep" aria-hidden="true">
            <i class="fas fa-chevron-right fa-xs"></i>
        </span>
        <span class="ob-breadcrumb-item {{ $loop->last ? 'ob-breadcrumb-current' : '' }}">
            @if (!$loop->last && !empty($item['url']))
                <a href="{{ $item['url'] }}">
                    @if (!empty($item['icon']))<i class="{{ $item['icon'] }} me-1"></i>@endif
                    {{ $item['label'] }}
                </a>
            @else
                @if (!empty($item['icon']))<i class="{{ $item['icon'] }} me-1"></i>@endif
                {{ $item['label'] }}
            @endif
        </span>
    @endforeach

</nav>
