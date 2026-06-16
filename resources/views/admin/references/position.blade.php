@extends('layout.app')

@section('title', 'Compétences — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Types de compétence', 'url' => route('admin.references.team')],
    ['label' => 'Compétences'],
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
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouvelle compétence</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.position.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3" style="font-size:var(--font-size-sm);">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Type <span class="text-danger">*</span></label>
                        <select name="EQ_ID" class="form-select form-select-sm" required style="width:180px;">
                            <option value="">— choisir —</option>
                            @foreach($teams as $t)
                                <option value="{{ $t->EQ_ID }}" @selected(old('EQ_ID', $filterEq) == $t->EQ_ID)>{{ $t->EQ_NOM }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Code court <span class="text-danger">*</span></label>
                        <input type="text" name="TYPE" value="{{ old('TYPE') }}"
                               class="form-control form-control-sm text-uppercase" maxlength="20" required
                               style="width:110px;" placeholder="PSE1">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">Description <span class="text-danger">*</span></label>
                        <input type="text" name="DESCRIPTION" value="{{ old('DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="60" required
                               placeholder="Premiers Secours en Équipe Niveau 1">
                    </div>
                </div>
                <div class="row g-2 align-items-center mb-2">
                    @foreach([
                        ['PS_FORMATION', 'Formation possible'],
                        ['PS_SECOURISME', 'Secourisme'],
                        ['PS_RECYCLE', 'Formation continue'],
                        ['PS_EXPIRABLE', 'Expirable'],
                        ['PS_DIPLOMA', 'Diplôme délivré'],
                        ['PS_AUDIT', 'Audit modif.'],
                        ['PS_USER_MODIFIABLE', 'Modif. utilisateur'],
                        ['PS_NATIONAL', 'National'],
                    ] as [$name, $label])
                    <div class="col-auto">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="{{ $name }}" id="new_{{ $name }}" value="1"
                                   @checked(old($name))>
                            <label class="form-check-label" for="new_{{ $name }}"
                                   style="font-size:var(--font-size-xs);">{{ $label }}</label>
                        </div>
                    </div>
                    @endforeach
                    <div class="col-auto">
                        <label class="form-label form-label-sm mb-0">Avert. expiration</label>
                        <select name="DAYS_WARNING" class="form-select form-select-sm" style="width:160px;">
                            <option value="0">Aucun</option>
                            <option value="7">7 jours avant</option>
                            <option value="30">1 mois avant</option>
                            <option value="60" selected>2 mois avant</option>
                            <option value="90">3 mois avant</option>
                            <option value="180">6 mois avant</option>
                            <option value="365">1 an avant</option>
                        </select>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Filter --}}
    <div class="d-flex align-items-center gap-2 mb-2">
        <span style="font-size:var(--font-size-sm);" class="text-muted">Filtrer :</span>
        <a href="{{ route('admin.references.position') }}"
           class="btn btn-sm {{ $filterEq == 0 ? 'btn-secondary' : 'btn-outline-secondary' }}">Tous</a>
        @foreach($teams as $t)
        <a href="{{ route('admin.references.position', ['eq' => $t->EQ_ID]) }}"
           class="btn btn-sm {{ $filterEq == $t->EQ_ID ? 'btn-secondary' : 'btn-outline-secondary' }}">
            {{ $t->EQ_NOM }}
        </a>
        @endforeach
    </div>

    {{-- List --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-list me-2"></i>Compétences ({{ $positions->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:140px;">Type</th>
                        <th style="width:100px;">Code</th>
                        <th>Description</th>
                        <th class="text-center" style="width:40px;" title="Formation possible"><i class="fas fa-graduation-cap"></i></th>
                        <th class="text-center" style="width:40px;" title="Secourisme"><i class="fas fa-first-aid"></i></th>
                        <th class="text-center" style="width:40px;" title="Formation continue"><i class="fas fa-sync"></i></th>
                        <th class="text-center" style="width:40px;" title="Expirable"><i class="fas fa-clock"></i></th>
                        <th class="text-center" style="width:40px;" title="Diplôme"><i class="fas fa-certificate"></i></th>
                        <th style="width:90px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($positions as $pos)
                    <tr>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-xs);">{{ $pos->PS_ID }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-xs);">{{ $pos->EQ_NOM }}</td>
                        <td class="align-middle font-monospace fw-semibold" style="font-size:var(--font-size-sm);">{{ $pos->TYPE }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $pos->DESCRIPTION }}</td>
                        <td class="text-center align-middle">
                            @if($pos->PS_FORMATION)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_SECOURISME)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_RECYCLE)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_EXPIRABLE)
                                <i class="fas fa-check text-success"
                                   title="{{ $pos->DAYS_WARNING > 0 ? 'Alerte '.$pos->DAYS_WARNING.' jours avant' : '' }}"></i>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_DIPLOMA)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="align-middle text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#editPoste{{ $pos->PS_ID }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.references.position.destroy', $pos->PS_ID) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer {{ addslashes($pos->TYPE) }} — {{ addslashes($pos->DESCRIPTION) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Edit modal --}}
                    <div class="modal fade" id="editPoste{{ $pos->PS_ID }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.references.position.update', $pos->PS_ID) }}">
                                    @csrf @method('PATCH')
                                    <div class="modal-header">
                                        <h6 class="modal-title">Modifier — {{ $pos->TYPE }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-auto">
                                                <label class="form-label form-label-sm">Type <span class="text-danger">*</span></label>
                                                <select name="EQ_ID" class="form-select form-select-sm" required>
                                                    @foreach($teams as $t)
                                                        <option value="{{ $t->EQ_ID }}" @selected($t->EQ_ID == $pos->EQ_ID)>{{ $t->EQ_NOM }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <label class="form-label form-label-sm">Code court <span class="text-danger">*</span></label>
                                                <input type="text" name="TYPE" value="{{ $pos->TYPE }}"
                                                       class="form-control form-control-sm text-uppercase" maxlength="20" required style="width:110px;">
                                            </div>
                                            <div class="col">
                                                <label class="form-label form-label-sm">Description <span class="text-danger">*</span></label>
                                                <input type="text" name="DESCRIPTION" value="{{ $pos->DESCRIPTION }}"
                                                       class="form-control form-control-sm" maxlength="60" required>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            @foreach([
                                                ['PS_FORMATION', 'Formation possible'],
                                                ['PS_SECOURISME', 'Secourisme'],
                                                ['PS_RECYCLE', 'Formation continue'],
                                                ['PS_EXPIRABLE', 'Expirable'],
                                                ['PS_DIPLOMA', 'Diplôme délivré'],
                                                ['PS_AUDIT', 'Audit modif.'],
                                                ['PS_USER_MODIFIABLE', 'Modif. utilisateur'],
                                                ['PS_NATIONAL', 'National'],
                                            ] as [$name, $label])
                                            <div class="col-auto">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="{{ $name }}" id="edit_{{ $pos->PS_ID }}_{{ $name }}"
                                                           value="1" @checked($pos->$name)>
                                                    <label class="form-check-label" for="edit_{{ $pos->PS_ID }}_{{ $name }}"
                                                           style="font-size:var(--font-size-sm);">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                            <div class="col-12 mt-2">
                                                <label class="form-label form-label-sm">Avertissement avant expiration</label>
                                                <select name="DAYS_WARNING" class="form-select form-select-sm" style="width:180px;">
                                                    @foreach([0=>'Aucun',7=>'7 jours',30=>'1 mois',60=>'2 mois',90=>'3 mois',180=>'6 mois',365=>'1 an'] as $days => $label)
                                                    <option value="{{ $days }}" @selected($pos->DAYS_WARNING == $days)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Aucune compétence.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
