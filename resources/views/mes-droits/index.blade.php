@extends('layout.app')

@section('title', 'Mes droits — ' . config('app.name'))

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ob-ctx-auto').forEach(function (el) {
        el.addEventListener('change', function () { this.closest('form').submit(); });
    });
    document.querySelectorAll('.ob-hab-cat-row').forEach(function (row) {
        row.addEventListener('click', function () {
            row.closest('tbody').classList.toggle('ob-hab-collapsed');
        });
    });
});
</script>
@endpush

@section('content')

@php $obsolete = $obsolete ?? []; @endphp

<x-ob-breadcrumb :items="[
    ['label' => 'Mon compte'],
    ['label' => 'Mes droits'],
]"/>

<div class="mx-3 mt-3">

    {{-- Page header --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-id-card me-2"></i>Mes droits</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                Aperçu en lecture seule de vos permissions effectives
            </div>
        </div>
        <div class="p-3">
            {{-- Section + role selectors (side by side), groups always-on --}}
            <div class="d-flex align-items-center flex-wrap gap-2">
                @feature('multi_site')
                <span class="text-muted" style="font-size:var(--font-size-sm);">Section&nbsp;:</span>
                <form method="GET" action="{{ route('mes-droits') }}" style="margin:0;">
                    <input type="hidden" name="role" value="{{ $roleId }}">
                    <select name="section" class="form-select form-select-sm ob-ctx-auto" style="width:auto;">
                        @forelse ($sections as $s)
                            <option value="{{ $s->S_ID }}" {{ (int) $s->S_ID === (int) $sectionId ? 'selected' : '' }}>{!! str_repeat('&nbsp;&nbsp;&nbsp;', (int) ($s->depth ?? 0)) !!}{{ ($s->depth ?? 0) > 0 ? '└ ' : '' }}{{ $s->S_DESCRIPTION }}</option>
                        @empty
                            <option>—</option>
                        @endforelse
                    </select>
                </form>
                @endfeature

                <span class="text-muted ms-2" style="font-size:var(--font-size-sm);">Rôle&nbsp;:</span>
                <form method="GET" action="{{ route('mes-droits') }}" style="margin:0;">
                    <input type="hidden" name="section" value="{{ $sectionId }}">
                    <select name="role" class="form-select form-select-sm ob-ctx-auto" style="width:auto;">
                        <option value="">Tous mes rôles</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}" {{ (int) $r->id === (int) $roleId ? 'selected' : '' }}>
                                {{ $r->name }}@if (!empty($r->inherited)) (hérité)@endif
                            </option>
                        @endforeach
                    </select>
                </form>

                <span class="ms-auto text-muted" style="font-size:var(--font-size-xs);">
                    <i class="fas fa-lock fa-xs me-1"></i>Vos groupes sont toujours appliqués
                </span>
            </div>
        </div>
    </div>

    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-list-check me-2"></i>Droits effectifs</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                <i class="fas fa-check text-success"></i> Effectif &nbsp;
                <i class="fas fa-minus text-muted"></i> Non accordé &nbsp;
                <i class="fas fa-lock text-muted"></i> Plafonné par la section
            </div>
        </div>
        <div class="p-3">
            <div class="ob-hab-matrix-scroll">
                <table class="ob-hab-table">
                    <thead>
                        <tr>
                            <th class="ob-hab-feat-head">Fonctionnalité</th>
                            <th class="ob-hab-colhead" style="min-width:80px;writing-mode:horizontal-tb;transform:none;">Accordé&nbsp;?</th>
                            <th class="ob-hab-colhead" style="min-width:160px;writing-mode:horizontal-tb;transform:none;text-align:left;padding:6px 8px;">Origine</th>
                        </tr>
                    </thead>
                    @foreach ($featuresByCategory as $category => $features)
                        <tbody data-hab-cat>
                            <tr class="ob-hab-cat-row">
                                <td colspan="3">
                                    <i class="fas fa-chevron-down ob-hab-chevron me-1"></i>{{ $category ?: 'Général' }}
                                    <span class="text-muted ms-1" style="font-weight:400;text-transform:none;">({{ $features->count() }})</span>
                                </td>
                            </tr>
                            @foreach ($features as $f)
                                @php
                                    $isDenied   = in_array((int) $f->F_ID, $denied, true);
                                    $sources    = $origins[(int) $f->F_ID] ?? [];
                                    $granted    = ! $isDenied && ! empty($sources);
                                    $isObsolete = in_array((int) $f->F_ID, $obsolete, true);
                                @endphp
                                <tr class="ob-hab-feat {{ $isDenied ? 'ob-hab-row-capped' : '' }}">
                                    <td class="ob-hab-feat-cell" title="{{ $f->F_DESCRIPTION }}">
                                        {{ $f->F_LIBELLE }}
                                        <span class="text-muted ms-1" style="font-size:10px;">#{{ $f->F_ID }}</span>
                                        @if ($f->F_FLAG)<span class="ob-badge ob-badge-bloqued ms-1" style="font-size:9px;">sensible</span>@endif
                                        @if ($isObsolete)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;" title="Fonctionnalité qui ne sera pas portée">obsolète</span>@endif
                                    </td>
                                    <td class="ob-hab-cell">
                                        @if ($isDenied)
                                            <i class="fas fa-lock text-muted" title="Plafonné par la section"></i>
                                        @elseif ($granted)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-minus text-muted"></i>
                                        @endif
                                    </td>
                                    <td class="ob-hab-feat-cell" style="position:static;min-width:0;font-size:11px;color:var(--text-muted-soft);">{{ $granted ? implode(' · ', $sources) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
