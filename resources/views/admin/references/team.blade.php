@extends('layout.app')

@section('title', 'Types de compétence — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Types de compétence'],
]"/>

<div class="mx-3 mt-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2 mb-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible py-2 mb-3">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouveau type de compétence</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.team.store') }}">
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
                        <label class="form-label form-label-sm">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="EQ_NOM" value="{{ old('EQ_NOM') }}"
                               class="form-control form-control-sm" maxlength="50" required
                               placeholder="Ex: Premiers secours">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Ordre</label>
                        <input type="number" name="EQ_ORDER" value="{{ old('EQ_ORDER', 10) }}"
                               class="form-control form-control-sm" min="0" max="9999" style="width:80px;" required>
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
            <div class="ob-widget-card-title"><i class="fas fa-users me-2"></i>Types de compétence ({{ $teams->count() }})</div>
            <a href="{{ route('admin.references.position') }}" class="btn btn-sm btn-light">
                <i class="fas fa-list me-1"></i> Voir les compétences
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Nom</th>
                        <th class="text-center" style="width:110px;">Compétences</th>
                        <th style="width:80px;">Ordre</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($teams as $team)
                    <tr>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-sm);">{{ $team->EQ_ID }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.references.team.update', $team->EQ_ID) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="EQ_NOM" value="{{ $team->EQ_NOM }}"
                                       class="form-control form-control-sm" maxlength="50" required style="min-width:200px;">
                                <input type="number" name="EQ_ORDER" value="{{ $team->EQ_ORDER }}"
                                       class="form-control form-control-sm" min="0" max="9999" style="width:70px;" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center align-middle">
                            <a href="{{ route('admin.references.position', ['eq' => $team->EQ_ID]) }}"
                               class="ob-badge ob-badge-int text-decoration-none">{{ $team->NB_POSTES }}</a>
                        </td>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-sm);">{{ $team->EQ_ORDER }}</td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.references.team.destroy', $team->EQ_ID) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($team->EQ_NOM) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" {{ $team->NB_POSTES > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun type de compétence.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
