@extends('layout.app')

@section('title', 'Fonctions activité — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.parametrage')],
    ['label' => 'Fonctions activité'],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouvelle fonction</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.parametrage.type-participation.store') }}">
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
                        <label class="form-label form-label-sm">Type d'activité <span class="text-danger">*</span></label>
                        <select name="TE_CODE" class="form-select form-select-sm" required style="min-width:180px;">
                            <option value="">— choisir —</option>
                            @foreach($eventTypes as $et)
                                <option value="{{ $et->TE_CODE }}" @selected(old('TE_CODE') == $et->TE_CODE)>
                                    {{ $et->TE_LIBELLE }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">N° <span class="text-danger">*</span></label>
                        <input type="number" name="TP_NUM" value="{{ old('TP_NUM', 1) }}"
                               class="form-control form-control-sm" style="width:70px;" min="1" max="99" required>
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="TP_LIBELLE" value="{{ old('TP_LIBELLE') }}"
                               class="form-control form-control-sm" maxlength="40" required>
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
            <div class="ob-widget-card-title"><i class="fas fa-users me-2"></i>Fonctions ({{ $items->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">N°</th>
                        <th>Libellé</th>
                        <th style="width:200px;">Type d'activité</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="align-middle text-center font-monospace" style="font-size:var(--font-size-sm);">{{ $item->TP_NUM }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.parametrage.type-participation.update', $item->TP_ID) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="number" name="TP_NUM" value="{{ $item->TP_NUM }}"
                                       class="form-control form-control-sm" style="width:65px;" min="1" max="99" required>
                                <input type="text" name="TP_LIBELLE" value="{{ $item->TP_LIBELLE }}"
                                       class="form-control form-control-sm" maxlength="40" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $item->te_label }}</td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.parametrage.type-participation.destroy', $item->TP_ID) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($item->TP_LIBELLE) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucune fonction.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
