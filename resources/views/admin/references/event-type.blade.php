@extends('layout.app')

@section('title', 'Types d\'activité — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Types d\'activité'],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouveau type d'activité</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.event-type.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3" style="font-size:var(--font-size-sm);">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Code <span class="text-danger">*</span></label>
                        <input type="text" name="TE_CODE" value="{{ old('TE_CODE') }}"
                               class="form-control form-control-sm text-uppercase" style="width:80px;"
                               maxlength="5" required placeholder="EX">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="TE_LIBELLE" value="{{ old('TE_LIBELLE') }}"
                               class="form-control form-control-sm" maxlength="40" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Catégorie <span class="text-danger">*</span></label>
                        <select name="CEV_CODE" class="form-select form-select-sm" required style="width:160px;">
                            <option value="">— choisir —</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->CEV_CODE }}" @selected(old('CEV_CODE') == $c->CEV_CODE)>
                                    {{ $c->CEV_DESCRIPTION }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto d-flex gap-3 pb-1">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="FICHE_PRESENCE" id="fp_new" value="1" {{ old('FICHE_PRESENCE') ? 'checked' : '' }}>
                            <label class="form-check-label" for="fp_new" style="font-size:var(--font-size-xs);">Fiche présence</label>
                        </div>
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="ORDRE_MISSION" id="om_new" value="1" {{ old('ORDRE_MISSION') ? 'checked' : '' }}>
                            <label class="form-check-label" for="om_new" style="font-size:var(--font-size-xs);">Ordre de mission</label>
                        </div>
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="CONVOCATIONS" id="conv_new" value="1" {{ old('CONVOCATIONS') ? 'checked' : '' }}>
                            <label class="form-check-label" for="conv_new" style="font-size:var(--font-size-xs);">Convocations</label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-calendar-alt me-2"></i>Types d'activité ({{ $items->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px;">Code</th>
                        <th>Libellé</th>
                        <th style="width:140px;">Catégorie</th>
                        <th class="text-center" style="width:110px;">Fiche présence</th>
                        <th class="text-center" style="width:120px;">Ordre mission</th>
                        <th class="text-center" style="width:110px;">Convocations</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="font-monospace align-middle fw-semibold" style="font-size:var(--font-size-sm);">
                            {{ $item->TE_CODE }}
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.references.event-type.update', $item->TE_CODE) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="TE_LIBELLE" value="{{ $item->TE_LIBELLE }}"
                                       class="form-control form-control-sm" maxlength="40" required style="min-width:160px;">
                                <select name="CEV_CODE" class="form-select form-select-sm" required style="width:130px;">
                                    @foreach($categories as $c)
                                        <option value="{{ $c->CEV_CODE }}" @selected($item->CEV_CODE === $c->CEV_CODE)>
                                            {{ $c->CEV_DESCRIPTION }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="FICHE_PRESENCE" value="0">
                                <input type="hidden" name="ORDRE_MISSION" value="0">
                                <input type="hidden" name="CONVOCATIONS" value="0">
                                <input class="form-check-input" type="checkbox" name="FICHE_PRESENCE" value="1" {{ $item->FICHE_PRESENCE ? 'checked' : '' }} style="margin-top:0;">
                                <input class="form-check-input" type="checkbox" name="ORDRE_MISSION" value="1" {{ $item->ORDRE_MISSION ? 'checked' : '' }} style="margin-top:0;">
                                <input class="form-check-input" type="checkbox" name="CONVOCATIONS" value="1" {{ $item->CONVOCATIONS ? 'checked' : '' }} style="margin-top:0;">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $item->CEV_DESCRIPTION }}</td>
                        <td class="text-center align-middle">
                            @if($item->FICHE_PRESENCE)<i class="fas fa-check text-success"></i>@else<i class="fas fa-times text-muted"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($item->ORDRE_MISSION)<i class="fas fa-check text-success"></i>@else<i class="fas fa-times text-muted"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($item->CONVOCATIONS)<i class="fas fa-check text-success"></i>@else<i class="fas fa-times text-muted"></i>@endif
                        </td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.references.event-type.destroy', $item->TE_CODE) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($item->TE_LIBELLE) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun type d'activité.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
