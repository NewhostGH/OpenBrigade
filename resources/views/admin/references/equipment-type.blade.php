@extends('layout.app')

@section('title', 'Types de matériel — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Types de matériel'],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouveau type de matériel</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.equipment-type.store') }}">
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
                        <input type="text" name="TM_CODE" value="{{ old('TM_CODE') }}"
                               class="form-control form-control-sm" style="width:130px;" maxlength="25" required>
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">Description <span class="text-danger">*</span></label>
                        <input type="text" name="TM_DESCRIPTION" value="{{ old('TM_DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="60" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Usage</label>
                        <input type="text" name="TM_USAGE" value="{{ old('TM_USAGE', 'DIVERS') }}"
                               class="form-control form-control-sm text-uppercase" style="width:120px;" maxlength="15"
                               placeholder="DIVERS">
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
            <div class="ob-widget-card-title"><i class="fas fa-toolbox me-2"></i>Types de matériel ({{ $items->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px;">Code</th>
                        <th>Description</th>
                        <th style="width:120px;">Usage</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @php $prevUsage = null; @endphp
                @forelse($items as $item)
                    @if($item->TM_USAGE !== $prevUsage)
                        <tr class="table-secondary">
                            <td colspan="4" class="py-1 fw-semibold" style="font-size:var(--font-size-xs);letter-spacing:.05em;">
                                {{ $item->TM_USAGE ?: 'Sans usage' }}
                            </td>
                        </tr>
                        @php $prevUsage = $item->TM_USAGE; @endphp
                    @endif
                    <tr>
                        <td class="font-monospace align-middle" style="font-size:var(--font-size-sm);">{{ $item->TM_CODE }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.references.equipment-type.update', $item->TM_ID) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="TM_CODE" value="{{ $item->TM_CODE }}"
                                       class="form-control form-control-sm" style="width:120px;" maxlength="25" required>
                                <input type="text" name="TM_DESCRIPTION" value="{{ $item->TM_DESCRIPTION }}"
                                       class="form-control form-control-sm" maxlength="60" required>
                                <input type="text" name="TM_USAGE" value="{{ $item->TM_USAGE }}"
                                       class="form-control form-control-sm text-uppercase" style="width:110px;" maxlength="15">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $item->TM_USAGE }}</td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.references.equipment-type.destroy', $item->TM_ID) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($item->TM_DESCRIPTION) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun type de matériel.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
