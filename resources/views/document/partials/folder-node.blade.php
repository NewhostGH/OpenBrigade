{{-- Recursive sidebar tree node. Vars (inherited from index): $folderId,
     $sectionId, $openFolders, $canManage. Passed per include: $node, $depth. --}}
@php
    $f = $node['folder'];
    $hasChildren = count($node['children']) > 0;
    $isOpen = in_array((int) $f->DF_ID, $openFolders, true);
    $isActive = $folderId === (int) $f->DF_ID;
@endphp
<div class="ob-doc-tree-node">
    <div class="ob-doc-folder {{ $isActive ? 'active' : '' }}" style="padding-left:{{ 0.4 + $depth * 0.85 }}rem;">
        @if ($hasChildren)
            <button type="button" class="ob-doc-tree-toggle {{ $isOpen ? 'open' : '' }}" data-tree-toggle aria-label="{{ __('document.folder_toggle_aria') }}">
                <i class="fas fa-chevron-right"></i>
            </button>
        @else
            <span class="ob-doc-tree-spacer"></span>
        @endif

        <a href="{{ route('document.index', ['folder' => $f->DF_ID, 'section' => $sectionId]) }}" class="ob-doc-folder-link">
            <i class="fas fa-folder fa-fw me-1" style="color:var(--color-folder)"></i>{{ $f->DF_NAME }}
        </a>

        @if ($canManage)
            <span class="ob-doc-folder-actions">
                <a href="{{ route('document.acl', ['folder', $f->DF_ID]) }}?window=1" class="btn btn-link btn-sm p-0 text-secondary" title="{{ __('document.folder_share_title') }}" data-acl-window>
                    <i class="fas fa-user-lock fa-xs"></i>
                </a>
                <button type="button" class="btn btn-link btn-sm p-0 text-secondary" title="{{ __('document.folder_rename_title') }}"
                        data-folder-edit data-id="{{ $f->DF_ID }}" data-name="{{ $f->DF_NAME }}">
                    <i class="fas fa-pen fa-xs"></i>
                </button>
                <form method="POST" action="{{ route('document.folder.destroy', $f->DF_ID) }}"
                      class="d-inline" data-folder-delete>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="{{ __('document.folder_delete_title') }}">
                        <i class="fas fa-trash fa-xs"></i>
                    </button>
                </form>
            </span>
        @endif
    </div>

    @if ($hasChildren)
        <div class="ob-doc-tree-children {{ $isOpen ? '' : 'd-none' }}">
            @foreach ($node['children'] as $child)
                @include('document.partials.folder-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
