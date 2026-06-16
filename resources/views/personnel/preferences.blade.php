@extends('layout.app')

@section('title', 'Préférences — ' . $personnel->P_PRENOM . ' ' . strtoupper($personnel->P_NOM) . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Personnel', 'url' => route('personnel.index')],
    ['label' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM, 'url' => route('personnel.show', $personnel)],
    ['label' => 'Préférences'],
]"/>

<div class="mx-3 mt-3" style="max-width:600px;">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2 mb-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('personnel.preferences.update', $personnel) }}">
        @csrf @method('PATCH')

        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-sliders-h me-1"></i>
                    @if(auth()->id() === $personnel->P_ID)
                        Mes préférences
                    @else
                        Préférences — {{ $personnel->P_PRENOM }} {{ strtoupper($personnel->P_NOM) }}
                    @endif
                </div>
                <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>

            {{-- Affichage --}}
            <div class="ob-widget-card-body border-bottom">
                <h6 class="fw-semibold mb-3" style="font-size:var(--font-size-sm);">
                    <i class="fas fa-desktop me-1 text-muted"></i> Affichage
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="pp_1" id="pp_1" value="1"
                                   @checked($values[1] == '1')>
                            <label class="form-check-label" for="pp_1" style="font-size:var(--font-size-sm);">
                                Afficher les info-bulles (tooltips)
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label" style="font-size:var(--font-size-sm);">Ordre de l'organigramme</label>
                        <select name="pp_4" class="form-select form-select-sm">
                            <option value="hierarchique" @selected($values[4] === 'hierarchique')>Ordre hiérarchique</option>
                            <option value="alphabetique" @selected($values[4] === 'alphabetique')>Ordre alphabétique</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label" style="font-size:var(--font-size-sm);">Éléments par page</label>
                        <select name="pp_15" class="form-select form-select-sm">
                            <option value="10" @selected($values[15] == '10')>10</option>
                            <option value="20" @selected($values[15] == '20')>20</option>
                            <option value="40" @selected($values[15] == '40')>40</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="ob-widget-card-footer text-end">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-save me-1"></i> Enregistrer
                </button>
            </div>
        </div>
    </form>
</div>

@endsection
