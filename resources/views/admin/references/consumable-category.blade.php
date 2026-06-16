@extends('layout.app')

@section('title', 'Catégories de consommable — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Catégories de consommable'],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouvelle catégorie</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.consumable-category.store') }}">
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
                        <input type="text" name="CC_CODE" value="{{ old('CC_CODE') }}"
                               class="form-control form-control-sm text-uppercase" style="width:110px;"
                               maxlength="12" placeholder="EX: MED" required
                               pattern="[A-Za-z0-9_]+" title="Lettres majuscules, chiffres et underscore uniquement">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="CC_NAME" value="{{ old('CC_NAME') }}"
                               class="form-control form-control-sm" style="width:160px;" maxlength="40" required>
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">Description</label>
                        <input type="text" name="CC_DESCRIPTION" value="{{ old('CC_DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="60">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Icône FA</label>
                        <input type="text" name="CC_IMAGE" value="{{ old('CC_IMAGE', 'boxes') }}"
                               class="form-control form-control-sm" style="width:110px;" maxlength="60"
                               placeholder="boxes">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Ordre</label>
                        <input type="number" name="CC_ORDER" value="{{ old('CC_ORDER', 0) }}"
                               class="form-control form-control-sm" style="width:80px;" min="0">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                </div>
                <div class="mt-2" style="font-size:var(--font-size-xs);color:var(--text-muted);">
                    Icône : nom FontAwesome (ex: <code>boxes</code>, <code>pills</code>, <code>gas-pump</code>).
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
                        <th style="width:110px;">Code</th>
                        <th style="width:160px;">Nom</th>
                        <th>Description</th>
                        <th style="width:110px;">Icône</th>
                        <th style="width:70px;">Ordre</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="font-monospace align-middle" style="font-size:var(--font-size-sm);">
                            {{ $item->CC_CODE }}
                            @if(($usageCounts[$item->CC_CODE] ?? 0) > 0)
                                <span class="ob-badge ob-badge-int ms-1">{{ $usageCounts[$item->CC_CODE] }}</span>
                            @endif
                        </td>
                        <td colspan="4" class="align-middle p-0">
                            <form method="POST" action="{{ route('admin.references.consumable-category.update', $item->CC_CODE) }}"
                                  class="d-flex gap-2 align-items-center p-1">
                                @csrf @method('PATCH')
                                <input type="text" name="CC_NAME" value="{{ $item->CC_NAME }}"
                                       class="form-control form-control-sm" style="width:150px;" maxlength="40" required>
                                <input type="text" name="CC_DESCRIPTION" value="{{ $item->CC_DESCRIPTION }}"
                                       class="form-control form-control-sm" maxlength="60">
                                <input type="text" name="CC_IMAGE" value="{{ $item->CC_IMAGE }}"
                                       class="form-control form-control-sm" style="width:100px;" maxlength="60"
                                       placeholder="boxes">
                                @if($item->CC_IMAGE)
                                    <i class="fas fa-{{ $item->CC_IMAGE }} text-muted flex-shrink-0"></i>
                                @endif
                                <input type="number" name="CC_ORDER" value="{{ $item->CC_ORDER }}"
                                       class="form-control form-control-sm" style="width:70px;" min="0">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle text-end">
                            @php $nb = $usageCounts[$item->CC_CODE] ?? 0; @endphp
                            @if($nb > 0)
                                <button class="btn btn-sm btn-outline-danger" disabled title="Utilisé par {{ $nb }} type(s)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <form method="POST" action="{{ route('admin.references.consumable-category.destroy', $item->CC_CODE) }}"
                                      onsubmit="return confirm('Supprimer la catégorie {{ addslashes($item->CC_NAME) }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune catégorie de consommable.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
