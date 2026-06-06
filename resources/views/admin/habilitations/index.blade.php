@extends('layout.app')

@section('title', 'Habilitations — ' . config('app.name'))

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ob-hab-toggle').forEach(function (cb) {
        cb.addEventListener('change', function () {
            this.closest('form').submit();
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

    {{-- Groups summary + add --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-id-badge me-2"></i>Groupes d'accès</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Nom</th>
                        <th style="width:110px;">Usage</th>
                        <th style="width:70px;" class="text-center">Ordre</th>
                        <th style="width:200px;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($groups as $g)
                    @php $system = in_array($g->GP_ID, [-1, 0, 4]); @endphp
                    <tr>
                        <td class="align-middle font-monospace fw-semibold" style="font-size:var(--font-size-sm);">
                            {{ $g->GP_ID }}
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            @if($system)
                                {{ $g->GP_DESCRIPTION }}
                                <span class="ob-badge ob-badge-archive ms-1" style="font-size:10px;">système</span>
                            @else
                                <form method="POST" action="{{ route('admin.habilitations.group.update', $g->GP_ID) }}"
                                      class="d-flex gap-2 align-items-center">
                                    @csrf @method('PATCH')
                                    <input type="text" name="GP_DESCRIPTION" value="{{ $g->GP_DESCRIPTION }}"
                                           class="form-control form-control-sm" style="width:160px;" maxlength="30" required>
                                    <select name="GP_USAGE" class="form-select form-select-sm" style="width:100px;">
                                        <option value="internes" @selected($g->GP_USAGE==='internes')>Internes</option>
                                        <option value="externes" @selected($g->GP_USAGE==='externes')>Externes</option>
                                        <option value="all"      @selected($g->GP_USAGE==='all')>Tous</option>
                                    </select>
                                    <input type="number" name="GP_ORDER" value="{{ $g->GP_ORDER }}"
                                           class="form-control form-control-sm" style="width:65px;" min="0" max="99">
                                    <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                            {{ $g->GP_USAGE }}
                        </td>
                        <td class="text-center align-middle" style="font-size:var(--font-size-sm);">{{ $g->GP_ORDER }}</td>
                        <td class="align-middle text-end">
                            @if(!$system)
                                <form method="POST" action="{{ route('admin.habilitations.group.destroy', $g->GP_ID) }}"
                                      onsubmit="return confirm('Supprimer le groupe {{ addslashes($g->GP_DESCRIPTION) }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                {{-- Add new group --}}
                <tr class="table-light">
                    <td colspan="5">
                        <form method="POST" action="{{ route('admin.habilitations.group.store') }}"
                              class="d-flex gap-2 align-items-center flex-wrap">
                            @csrf
                            @if($errors->any())
                                <div class="w-100 text-danger" style="font-size:var(--font-size-xs);">
                                    {{ $errors->first() }}
                                </div>
                            @endif
                            <input type="number" name="GP_ID" placeholder="ID" class="form-control form-control-sm"
                                   style="width:70px;" min="1" required>
                            <input type="text" name="GP_DESCRIPTION" placeholder="Nom du groupe"
                                   class="form-control form-control-sm" style="width:180px;" maxlength="30" required>
                            <select name="GP_USAGE" class="form-select form-select-sm" style="width:100px;">
                                <option value="internes">Internes</option>
                                <option value="externes">Externes</option>
                                <option value="all">Tous</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Ajouter
                            </button>
                        </form>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Permission matrix --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-th me-2"></i>Matrice des permissions</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                Les cases cochées indiquent les permissions accordées au groupe
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="table table-sm table-bordered mb-0" style="min-width:600px;">
                <thead>
                    <tr>
                        <th style="min-width:220px;background:var(--card-subheader-bg);">Permission</th>
                        @foreach($groups as $g)
                            <th class="text-center" style="min-width:80px;background:var(--card-subheader-bg);font-size:var(--font-size-xs);writing-mode:vertical-rl;transform:rotate(180deg);white-space:nowrap;padding:8px 4px;vertical-align:bottom;">
                                {{ $g->GP_DESCRIPTION }}
                                <span style="opacity:.6;">({{ $g->GP_ID }})</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($featuresByCategory as $category => $features)
                    <tr>
                        <td colspan="{{ $groups->count() + 1 }}"
                            style="background:var(--card-subheader-bg);font-size:var(--font-size-xs);font-weight:600;letter-spacing:.04em;text-transform:uppercase;padding:4px 8px;">
                            {{ $category ?: 'Général' }}
                        </td>
                    </tr>
                    @foreach($features as $f)
                        <tr>
                            <td style="font-size:var(--font-size-xs);vertical-align:middle;"
                                title="{{ $f->F_DESCRIPTION }}">
                                <span>{{ $f->F_LIBELLE }}</span>
                                <span class="text-muted ms-1" style="font-size:10px;">#{{ $f->F_ID }}</span>
                                @if($f->F_FLAG)<span class="ob-badge ob-badge-bloqued ms-1" style="font-size:9px;">sensible</span>@endif
                            </td>
                            @foreach($groups as $g)
                                @php
                                    $key = "{$g->GP_ID}|{$f->F_ID}";
                                    $isGranted = $granted->has($key);
                                    $isSystem  = in_array($g->GP_ID, [-1, 0, 4]);
                                @endphp
                                <td class="text-center align-middle" style="padding:2px;">
                                    @if($isSystem)
                                        {{-- System groups: read-only --}}
                                        @if($isGranted)
                                            <i class="fas fa-check text-success" style="font-size:12px;"></i>
                                        @else
                                            <i class="fas fa-minus text-muted" style="font-size:10px;"></i>
                                        @endif
                                    @else
                                        <form method="POST" action="{{ route('admin.habilitations.toggle') }}" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="GP_ID" value="{{ $g->GP_ID }}">
                                            <input type="hidden" name="F_ID"  value="{{ $f->F_ID }}">
                                            <input type="hidden" name="grant" value="{{ $isGranted ? '0' : '1' }}">
                                            <input class="form-check-input ob-hab-toggle" type="checkbox"
                                                   style="cursor:pointer;"
                                                   {{ $isGranted ? 'checked' : '' }}>
                                        </form>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
