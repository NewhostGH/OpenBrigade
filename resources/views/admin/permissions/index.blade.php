@extends('layout.app')

@section('title', 'Permissions — ' . config('app.name'))

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-submit toggles and selectors.
    document.querySelectorAll('.ob-hab-auto').forEach(function (cb) {
        cb.addEventListener('change', function () { this.closest('form').submit(); });
    });

    document.querySelectorAll('[data-hab-matrix]').forEach(function (matrix) {
        // Column show/hide pills.
        matrix.querySelectorAll('.ob-hab-pill').forEach(function (pill) {
            pill.addEventListener('click', function () {
                var col = pill.dataset.col;
                if (col === 'all') {
                    matrix.querySelectorAll('.ob-hab-pill').forEach(p => p.classList.add('active'));
                    matrix.querySelectorAll('[data-col]').forEach(c => c.classList.remove('ob-hab-hidden'));
                    return;
                }
                pill.classList.toggle('active');
                var show = pill.classList.contains('active');
                matrix.querySelectorAll('[data-col="' + col + '"]').forEach(function (c) {
                    c.classList.toggle('ob-hab-hidden', !show);
                });
                var allOn = Array.from(matrix.querySelectorAll('.ob-hab-pill:not(.ob-hab-pill-all)'))
                    .every(p => p.classList.contains('active'));
                var allPill = matrix.querySelector('.ob-hab-pill-all');
                if (allPill) { allPill.classList.toggle('active', allOn); }
            });
        });
    });

    // Collapsible category sections (any rights matrix on the page).
    document.querySelectorAll('.ob-hab-cat-row').forEach(function (row) {
        row.addEventListener('click', function () {
            row.closest('tbody').classList.toggle('ob-hab-collapsed');
        });
    });
});
</script>
@endpush

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Permissions'],
]"/>

<div class="mx-3 mt-3">

    {{-- Flash messages are rendered globally by layout.app --}}

    {{-- Tabs : Plafonds, Groupes, Rôles --}}
    <ul class="nav nav-tabs" role="tablist">
        @feature('multi_site')
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'ceiling' ? 'active' : '' }}"
               href="{{ route('admin.permissions', ['tab' => 'ceiling', 'section' => $sectionId]) }}">
                <i class="fas fa-layer-group me-1"></i>{{ __('admin.permissions.tab_ceiling') }}
            </a>
        </li>
        @endfeature
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'groups' ? 'active' : '' }}"
               href="{{ route('admin.permissions', ['tab' => 'groups', 'section' => $sectionId]) }}">
                <i class="fas fa-key me-1"></i>{{ __('admin.permissions.tab_groups') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'roles' ? 'active' : '' }}"
               href="{{ route('admin.permissions', ['tab' => 'roles', 'section' => $sectionId]) }}">
                <i class="fas fa-user-tie me-1"></i>{{ __('admin.permissions.tab_roles') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'overrides' ? 'active' : '' }}"
               href="{{ route('admin.permissions', ['tab' => 'overrides']) }}">
                <i class="fas fa-user-shield me-1"></i>{{ __('admin.permissions.tab_overrides') }}
            </a>
        </li>
    </ul>

    <div class="border border-top-0 rounded-bottom bg-white p-3">

        @if ($tab === 'ceiling')
            @include('admin.permissions.partials.ceiling')
        @elseif ($tab === 'groups')
            @include('admin.permissions.partials.matrix', [
                'kind'    => 'group',
                'columns' => $groups,
                'title'   => "Groupes d'accès globaux",
                'hint'    => 'Droits globaux, transverses à toutes les sections, mais plafonnés par chaque section.',
            ])
        @elseif ($tab === 'roles')
            @include('admin.permissions.partials.matrix', [
                'kind'    => 'role',
                'columns' => $roles,
                'title'   => 'Rôles organisationnels',
                'hint'    => 'Rôles attribués par section. Les fonctionnalités refusées par la section sont verrouillées.',
            ])
        @else
            @include('admin.permissions.partials.overrides')
        @endif

    </div>-
</div>

@endsection
