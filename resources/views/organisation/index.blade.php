@extends('layout.app')

@section('title', 'Organisation — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Organisation'],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Organisation</h1>
        @if(auth()->user()->hasPermission(55))
            {{-- TODO: Migrate code --}}
            <a href="{{ url('/legacy/ins_section.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle section
            </a>
        @endif
    </div>
</div>

<div class="mx-3 mt-3">
    <div class="ob-org-tree">
        @include('organisation._node', ['nodes' => $tree, 'currentSectionId' => $sectionId])
    </div>
</div>

@endsection
