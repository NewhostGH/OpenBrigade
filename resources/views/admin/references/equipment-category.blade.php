@extends('layout.app')

@section('title', 'Catégories de matériel — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Catégories de matériel'],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouvelle catégorie</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.equipment-category.store') }}">
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
                        <input type="text" name="TM_USAGE" value="{{ old('TM_USAGE') }}"
                               class="form-control form-control-sm text-uppercase" style="width:130px;"
                               maxlength="15" placeholder="EX: DIVERS" required
                               pattern="[A-Za-z0-9_]+" title="Lettres majuscules, chiffres et underscore uniquement">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">Description <span class="text-danger">*</span></label>
                        <input type="text" name="CM_DESCRIPTION" value="{{ old('CM_DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="60" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Icône FA</label>
                        <input type="text" name="PICTURE" value="{{ old('PICTURE', 'cog') }}"
                               class="form-control form-control-sm" style="width:130px;" maxlength="60"
                               placeholder="cog">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                </div>
                <div class="mt-2" style="font-size:var(--font-size-xs);color:var(--text-muted);">
                    Icône : nom FontAwesome (ex: <code>cog</code>, <code>fire</code>, <code>medkit</code>).
                    <a href="https://fontawesome.com/icons?d=gallery&m=free" target="_blank" rel="noreferrer">Parcourir les icônes</a>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-layer-group me-2"></i>Catégories ({{ $items->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px;">Code</th>
                        <th>Description</th>
                        <th style="width:100px;">Icône</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="font-monospace align-middle" style="font-size:var(--font-size-sm);">
                            {{ $item->TM_USAGE }}
                        </td>
                        <td class="align-middle">
                            <form method="POST" action="{{ route('admin.references.equipment-category.update', $item->TM_USAGE) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="CM_DESCRIPTION" value="{{ $item->CM_DESCRIPTION }}"
                                       class="form-control form-control-sm" maxlength="60" required>
                                <input type="text" name="PICTURE" value="{{ $item->PICTURE }}"
                                       class="form-control form-control-sm" style="width:120px;" maxlength="60">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            @if($item->PICTURE)
                                <i class="fas fa-{{ $item->PICTURE }} me-1"></i>
                                <span class="text-muted">{{ $item->PICTURE }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.references.equipment-category.destroy', $item->TM_USAGE) }}"
                                  onsubmit="return confirm('Supprimer la catégorie {{ addslashes($item->CM_DESCRIPTION) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucune catégorie de matériel.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
