<ul>
    @foreach($nodes as $node)
        <li class="ob-org-node">
            {{-- TODO: Migrate code --}}
            <a href="{{ url('/legacy/upd_section.php?S_ID=' . $node['section']->S_ID) }}"
               class="ob-org-node-card {{ $node['section']->S_ID == $currentSectionId ? 'current' : '' }}">
                <i class="fas fa-layer-group fa-xs" style="color:var(--text-muted-soft)"></i>
                <span>{{ $node['section']->S_CODE }}</span>
                @if($node['section']->S_DESCRIPTION)
                    <span class="text-muted">— {{ $node['section']->S_DESCRIPTION }}</span>
                @endif
                <span class="ob-org-node-count">{{ $node['count'] }} membres</span>
            </a>

            @if(!empty($node['children']))
                <div class="ob-org-connector">
                    @include('organisation._node', ['nodes' => $node['children'], 'currentSectionId' => $currentSectionId])
                </div>
            @endif
        </li>
    @endforeach
</ul>
