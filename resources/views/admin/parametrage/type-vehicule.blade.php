@extends('layout.app')

@section('title', 'Types de véhicule — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.parametrage')],
    ['label' => 'Types de véhicule'],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouveau type de véhicule</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.parametrage.type-vehicule.store') }}">
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
                        <input type="text" name="TV_CODE" value="{{ old('TV_CODE') }}"
                               class="form-control form-control-sm text-uppercase" style="width:90px;"
                               maxlength="10" required placeholder="VSAV">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="TV_LIBELLE" value="{{ old('TV_LIBELLE') }}"
                               class="form-control form-control-sm" maxlength="60" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Usage <span class="text-danger">*</span></label>
                        <input type="text" name="TV_USAGE" value="{{ old('TV_USAGE') }}"
                               class="form-control form-control-sm text-uppercase" style="width:110px;" maxlength="12" required
                               placeholder="SECOURS">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Nb par défaut</label>
                        <input type="number" name="TV_NB" value="{{ old('TV_NB', 0) }}"
                               class="form-control form-control-sm" style="width:80px;" min="0">
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
            <div class="ob-widget-card-title"><i class="fas fa-truck me-2"></i>Types de véhicule ({{ $items->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px;">Code</th>
                        <th>Libellé</th>
                        <th style="width:120px;">Usage</th>
                        <th style="width:80px;" class="text-center">Nb défaut</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @php $prevUsage = null; @endphp
                @forelse($items as $item)
                    @if($item->TV_USAGE !== $prevUsage)
                        <tr class="table-secondary">
                            <td colspan="5" class="py-1 fw-semibold" style="font-size:var(--font-size-xs);letter-spacing:.05em;">
                                {{ $item->TV_USAGE ?: 'Sans usage' }}
                            </td>
                        </tr>
                        @php $prevUsage = $item->TV_USAGE; @endphp
                    @endif
                    <tr>
                        <td class="font-monospace align-middle fw-semibold" style="font-size:var(--font-size-sm);">{{ $item->TV_CODE }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.parametrage.type-vehicule.update', $item->TV_CODE) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="TV_LIBELLE" value="{{ $item->TV_LIBELLE }}"
                                       class="form-control form-control-sm" maxlength="60" required style="min-width:160px;">
                                <input type="text" name="TV_USAGE" value="{{ $item->TV_USAGE }}"
                                       class="form-control form-control-sm text-uppercase" style="width:100px;" maxlength="12" required>
                                <input type="number" name="TV_NB" value="{{ $item->TV_NB ?? 0 }}"
                                       class="form-control form-control-sm" style="width:70px;" min="0">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $item->TV_USAGE }}</td>
                        <td class="text-center align-middle" style="font-size:var(--font-size-sm);">{{ $item->TV_NB ?? 0 }}</td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.parametrage.type-vehicule.destroy', $item->TV_CODE) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($item->TV_CODE) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun type de véhicule.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
