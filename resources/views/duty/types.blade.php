@extends('layout.app')

@section('title', 'Types de garde — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Garde', 'url' => route('duty.index')],
    ['label' => 'Types de garde'],
]"/>

<div class="mx-3 mt-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2 mb-3 fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible py-2 mb-3 fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-shield-alt me-2"></i>Types de garde ({{ $items->count() }})</div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus me-1"></i> Nouveau type
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:32px;" class="text-center">#</th>
                        <th>Nom</th>
                        <th style="width:80px;" class="text-center">Jour</th>
                        <th style="width:80px;" class="text-center">Nuit</th>
                        <th style="width:120px;" class="text-center">Personnes J/N</th>
                        <th style="width:100px;" class="text-center">Véhicules</th>
                        <th style="width:70px;" class="text-center">Défaut</th>
                        <th style="width:100px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="text-center text-muted align-middle" style="font-size:var(--font-size-xs);">{{ $item->EQ_ORDER }}</td>
                        <td class="align-middle fw-semibold" style="font-size:var(--font-size-sm);">
                            {{ $item->EQ_NOM }}
                            @if($item->EQ_LIEU)
                                <div class="text-muted fw-normal" style="font-size:var(--font-size-xs);">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $item->EQ_LIEU }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($item->EQ_JOUR)
                                <i class="fas fa-sun text-warning" title="Actif le jour"></i>
                                <div class="text-muted" style="font-size:var(--font-size-xs);">
                                    {{ substr($item->EQ_DEBUT1 ?? '', 0, 5) }}–{{ substr($item->EQ_FIN1 ?? '', 0, 5) }}
                                </div>
                            @else
                                <i class="fas fa-minus text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($item->EQ_NUIT)
                                <i class="fas fa-moon text-primary" title="Actif la nuit"></i>
                                <div class="text-muted" style="font-size:var(--font-size-xs);">
                                    {{ substr($item->EQ_DEBUT2 ?? '', 0, 5) }}–{{ substr($item->EQ_FIN2 ?? '', 0, 5) }}
                                </div>
                            @else
                                <i class="fas fa-minus text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center align-middle" style="font-size:var(--font-size-sm);">
                            {{ $item->EQ_PERSONNEL1 ?? 0 }} / {{ $item->EQ_PERSONNEL2 ?? 0 }}
                        </td>
                        <td class="text-center align-middle">
                            @if($item->EQ_VEHICULES)
                                <i class="fas fa-check text-success"></i>
                            @else
                                <i class="fas fa-minus text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($item->EQ_DEFAULT)
                                <i class="fas fa-star text-warning" title="Type par défaut"></i>
                            @endif
                        </td>
                        <td class="text-end align-middle">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $item->EQ_ID }}"
                                    title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('duty.types.destroy', $item->EQ_ID) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer le type « {{ addslashes($item->EQ_NOM) }} » ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Aucun type de garde configuré pour votre section.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Create modal ──────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('duty.types.store') }}">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="createModalLabel">
                        <i class="fas fa-plus me-1"></i> Nouveau type de garde
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('duty.partials.type-form', ['item' => null, 'nextOrder' => $nextOrder])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit modals ───────────────────────────────────────────────────────────── --}}
@foreach($items as $item)
<div class="modal fade" id="editModal{{ $item->EQ_ID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('duty.types.update', $item->EQ_ID) }}">
                @csrf @method('PATCH')
                <div class="modal-header py-2">
                    <h6 class="modal-title">
                        <i class="fas fa-edit me-1"></i> Modifier — {{ $item->EQ_NOM }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('duty.partials.type-form', ['item' => $item, 'nextOrder' => $nextOrder])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
