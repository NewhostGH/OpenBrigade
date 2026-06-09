@extends('layout.app')

@section('title', 'Habilitations — ' . config('app.name'))

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
    ['label' => 'Habilitations'],
]"/>

<div class="mx-3 mt-3">

    {{-- Flash messages are rendered globally by layout.app --}}

    {{-- Tabs : Plafonds, Groupes, Rôles --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'ceiling' ? 'active' : '' }}"
               href="{{ route('admin.habilitations', ['tab' => 'ceiling', 'section' => $sectionId]) }}">
                <i class="fas fa-layer-group me-1"></i>Plafonds par section
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'groups' ? 'active' : '' }}"
               href="{{ route('admin.habilitations', ['tab' => 'groups', 'section' => $sectionId]) }}">
                <i class="fas fa-key me-1"></i>Groupes d'accès
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'roles' ? 'active' : '' }}"
               href="{{ route('admin.habilitations', ['tab' => 'roles', 'section' => $sectionId]) }}">
                <i class="fas fa-user-tie me-1"></i>Rôles organisationnels
            </a>
        </li>
    </ul>

    <div class="border border-top-0 rounded-bottom bg-white p-3">

        @if ($tab === 'ceiling')
            @include('admin.habilitations.partials.ceiling')
        @elseif ($tab === 'groups')
            @include('admin.habilitations.partials.matrix', [
                'kind'    => 'group',
                'columns' => $groups,
                'title'   => "Groupes d'accès globaux",
                'hint'    => 'Droits globaux, transverses à toutes les sections, mais plafonnés par chaque section.',
            ])
        @else
            @include('admin.habilitations.partials.matrix', [
                'kind'    => 'role',
                'columns' => $roles,
                'title'   => 'Rôles organisationnels',
                'hint'    => 'Rôles attribués par section. Les fonctionnalités refusées par la section sont verrouillées.',
            ])
        @endif

    </div>

    <p class="text-muted small mt-3">
        <i class="fas fa-circle-info me-1"></i>
        L'attribution <em>personne → section → rôle</em> se fait depuis la fiche du personnel.
        L'appartenance aux groupes globaux reste sur la fiche (groupe principal / secondaire).
    </p>
</div>

@endsection
