@extends('layout.app')

@section('title', 'Types de consommable — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.parametrage')],
    ['label' => 'Types de consommable'],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouveau type de consommable</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.parametrage.type-consommable.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3" style="font-size:var(--font-size-sm);">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-2 align-items-end">
                    <div class="col">
                        <label class="form-label form-label-sm">Description <span class="text-danger">*</span></label>
                        <input type="text" name="TC_DESCRIPTION" value="{{ old('TC_DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="60" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Catégorie <span class="text-danger">*</span></label>
                        <input type="text" name="CC_CODE" value="{{ old('CC_CODE') }}"
                               class="form-control form-control-sm text-uppercase" style="width:100px;" maxlength="12" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Cond. <span class="text-danger">*</span></label>
                        <select name="TC_CONDITIONNEMENT" class="form-select form-select-sm" required style="width:90px;">
                            <option value="UN" @selected(old('TC_CONDITIONNEMENT')=='UN')>UN</option>
                            <option value="LT" @selected(old('TC_CONDITIONNEMENT')=='LT')>LT</option>
                            <option value="KG" @selected(old('TC_CONDITIONNEMENT')=='KG')>KG</option>
                            <option value="BT" @selected(old('TC_CONDITIONNEMENT')=='BT')>BT</option>
                            <option value="BO" @selected(old('TC_CONDITIONNEMENT')=='BO')>BO</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Unité <span class="text-danger">*</span></label>
                        <select name="TC_UNITE_MESURE" class="form-select form-select-sm" required style="width:90px;">
                            <option value="UN" @selected(old('TC_UNITE_MESURE')=='UN')>UN</option>
                            <option value="LT" @selected(old('TC_UNITE_MESURE')=='LT')>LT</option>
                            <option value="KG" @selected(old('TC_UNITE_MESURE')=='KG')>KG</option>
                            <option value="ML" @selected(old('TC_UNITE_MESURE')=='ML')>ML</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Qté/unité <span class="text-danger">*</span></label>
                        <input type="number" name="TC_QUANTITE_PAR_UNITE" value="{{ old('TC_QUANTITE_PAR_UNITE', 1) }}"
                               class="form-control form-control-sm" style="width:80px;" min="0" step="0.01" required>
                    </div>
                    <div class="col-auto d-flex align-items-center gap-2 pb-1">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="TC_PEREMPTION" id="permp_new" value="1" {{ old('TC_PEREMPTION') ? 'checked' : '' }}>
                            <label class="form-check-label" for="permp_new" style="font-size:var(--font-size-xs);">Péremption</label>
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
            <div class="ob-widget-card-title"><i class="fas fa-boxes me-2"></i>Types de consommable ({{ $items->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px;">Catégorie</th>
                        <th>Description</th>
                        <th style="width:80px;" class="text-center">Cond.</th>
                        <th style="width:80px;" class="text-center">Unité</th>
                        <th style="width:90px;" class="text-center">Qté/unité</th>
                        <th style="width:90px;" class="text-center">Péremption</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="font-monospace align-middle" style="font-size:var(--font-size-sm);">{{ $item->CC_CODE }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.parametrage.type-consommable.update', $item->TC_ID) }}"
                                  class="d-flex gap-2 align-items-center flex-wrap">
                                @csrf @method('PATCH')
                                <input type="text" name="CC_CODE" value="{{ $item->CC_CODE }}"
                                       class="form-control form-control-sm text-uppercase" style="width:90px;" maxlength="12" required>
                                <input type="text" name="TC_DESCRIPTION" value="{{ $item->TC_DESCRIPTION }}"
                                       class="form-control form-control-sm" maxlength="60" required style="min-width:140px;">
                                <select name="TC_CONDITIONNEMENT" class="form-select form-select-sm" style="width:80px;">
                                    @foreach(['UN','LT','KG','BT','BO'] as $opt)
                                        <option value="{{ $opt }}" @selected($item->TC_CONDITIONNEMENT==$opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                <select name="TC_UNITE_MESURE" class="form-select form-select-sm" style="width:80px;">
                                    @foreach(['UN','LT','KG','ML'] as $opt)
                                        <option value="{{ $opt }}" @selected($item->TC_UNITE_MESURE==$opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="TC_QUANTITE_PAR_UNITE" value="{{ $item->TC_QUANTITE_PAR_UNITE }}"
                                       class="form-control form-control-sm" style="width:75px;" min="0" step="0.01" required>
                                <input type="hidden" name="TC_PEREMPTION" value="0">
                                <input class="form-check-input" type="checkbox" name="TC_PEREMPTION" value="1" {{ $item->TC_PEREMPTION ? 'checked' : '' }} style="margin-top:0;" title="Péremption">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center align-middle" style="font-size:var(--font-size-sm);">{{ $item->TC_CONDITIONNEMENT }}</td>
                        <td class="text-center align-middle" style="font-size:var(--font-size-sm);">{{ $item->TC_UNITE_MESURE }}</td>
                        <td class="text-center align-middle" style="font-size:var(--font-size-sm);">{{ $item->TC_QUANTITE_PAR_UNITE }}</td>
                        <td class="text-center align-middle">
                            @if($item->TC_PEREMPTION)<i class="fas fa-check text-success"></i>@else<i class="fas fa-times text-muted"></i>@endif
                        </td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.parametrage.type-consommable.destroy', $item->TC_ID) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($item->TC_DESCRIPTION) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun type de consommable.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
