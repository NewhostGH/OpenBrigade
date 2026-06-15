@foreach($nodes as $node)
<li>
    <a href="{{ route('organization.sections.show', $node['section']->S_ID) }}"
       class="ob-org-card{{ $node['section']->S_ID == $currentSectionId ? ' ob-org-card--current' : '' }}">
        <span class="ob-org-card__code">{{ $node['section']->S_CODE }}</span>
        @if($node['section']->S_DESCRIPTION)
            <span class="ob-org-card__name">{{ $node['section']->S_DESCRIPTION }}</span>
        @endif
        <span class="ob-org-card__count">
            {{ $node['count'] }}&nbsp;membre{{ $node['count'] !== 1 ? 's' : '' }}
        </span>
    </a>

    @if(!empty($node['children']))
        <ul>
            @include('organization._node', [
                'nodes'            => $node['children'],
                'currentSectionId' => $currentSectionId,
            ])
        </ul>
    @endif
</li>
@endforeach
