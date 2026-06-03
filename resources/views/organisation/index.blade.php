@extends('layout.app')

@section('title', 'Organisation — ' . config('app.name'))

@push('styles')
<style>
.org-tree ul { list-style: none; padding-left: 20px; margin: 0; }
.org-tree > ul { padding-left: 0; }
.org-node { padding: 4px 0; }
.org-node-card {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--component-bg); border: 1px solid var(--component-border);
    border-radius: var(--radius-md); padding: 6px 12px;
    font-size: var(--font-size-sm); text-decoration: none; color: inherit;
    transition: box-shadow var(--transition-fast);
}
.org-node-card:hover { box-shadow: 0 2px 6px rgba(0,0,0,0.08); text-decoration: none; color: inherit; }
.org-node-card.current { border-color: var(--brand-bg); font-weight: 600; }
.org-node-count { font-size: var(--font-size-xs); color: var(--text-muted-soft);
    background: var(--page-bg); border-radius: 10px; padding: 1px 6px; }
.org-connector { border-left: 2px solid var(--component-border); padding-left: 16px; margin-left: 16px; }
</style>
@endpush

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Organisation'],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Organisation</h1>
        @if(auth()->user()->hasPermission(55))
            <a href="{{ url('/legacy/ins_section.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle section
            </a>
        @endif
    </div>
</div>

<div class="mx-3 mt-3">
    <div class="org-tree">
        @include('organisation._node', ['nodes' => $tree, 'currentSectionId' => $sectionId])
    </div>
</div>

@endsection
