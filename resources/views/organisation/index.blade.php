@extends('layout.app')

@section('title', 'Organigramme — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Organisation'],
    ['label' => 'Organigramme'],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-project-diagram me-2"></i>Organigramme
            </div>
            <div class="ob-widget-card-actions">
                <a href="{{ route('organisation.sections') }}" class="btn btn-sm btn-outline-secondary me-1">
                    <i class="fas fa-layer-group me-1"></i>Gérer les sections
                </a>
                @if(auth()->user()->hasPermission(55))
                    <a href="{{ route('organisation.sections.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Nouvelle section
                    </a>
                @endif
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            <div class="ob-org-chart-wrap">
                <div id="ob-org-tree"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
window.__OB_ORG_TREE__        = @json($tree);
window.__OB_CURRENT_SECTION__ = {{ $sectionId }};
</script>
@vite(['resources/js/ob-organisation-organigramme.js'])
@endpush
