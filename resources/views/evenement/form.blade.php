@extends('layout.app')

@section('title', ($event ? 'Modifier' : 'Nouvelle activité') . ' — ' . config('app.name'))

@section('content')

@php
    $isEdit     = $event !== null;
    $formAction = $isEdit ? route('evenement.update', $event->E_CODE) : route('evenement.store');
    $user       = auth()->user();
    $userSection = (int) $user->P_SECTION;

    // Pre-fill values from existing event or defaults
    $val = fn(string $field, $default = '') => old($field, $isEdit ? ($event->$field ?? $default) : $default);
    $horVal = fn(string $field, $default = '') => old($field, $horaire ? ($horaire->$field ?? $default) : $default);
@endphp

<x-ob-breadcrumb :items="[
    ['label' => 'Activités', 'url' => route('evenement.index')],
    @if ($isEdit)
        ['label' => $event->E_LIBELLE ?? $event->E_CODE, 'url' => route('evenement.show', $event->E_CODE)],
        ['label' => 'Modifier'],
    @else
        ['label' => 'Nouvelle activité'],
    @endif
]"/>

<div class="mx-3 mt-3" style="max-width:760px;">

    <div class="widget-card">
        <div class="widget-card-header">
            <div class="widget-card-title">
                <i class="fas fa-{{ $isEdit ? 'edit' : 'plus' }} me-1"></i>
                {{ $isEdit ? 'Modifier l\'activité' : 'Nouvelle activité' }}
            </div>
        </div>

        <div class="widget-card-body">

            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                {{-- ── Type + Title ──────────────────────────────────────────── --}}
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold" for="TE_CODE">
                            Type <span class="text-danger">*</span>
                        </label>
                        <select id="TE_CODE" name="TE_CODE"
                                class="form-select form-select-sm @error('TE_CODE') is-invalid @enderror"
                                required>
                            <option value="">— Choisir —</option>
                            @foreach ($types as $t)
                                <option value="{{ $t->TE_CODE }}"
                                        {{ $val('TE_CODE') === $t->TE_CODE ? 'selected' : '' }}>
                                    {{ $t->TE_LIBELLE }}
                                </option>
                            @endforeach
                        </select>
                        @error('TE_CODE')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label fw-semibold" for="E_LIBELLE">
                            Intitulé <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="E_LIBELLE" name="E_LIBELLE"
                               class="form-control form-control-sm @error('E_LIBELLE') is-invalid @enderror"
                               value="{{ $val('E_LIBELLE') }}"
                               maxlength="255" required autofocus>
                        @error('E_LIBELLE')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ── Lieu + Section ───────────────────────────────────────── --}}
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold" for="E_LIEU">Lieu</label>
                        <input type="text" id="E_LIEU" name="E_LIEU"
                               class="form-control form-control-sm @error('E_LIEU') is-invalid @enderror"
                               value="{{ $val('E_LIEU') }}"
                               maxlength="255">
                        @error('E_LIEU')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold" for="S_ID">Section</label>
                        <select id="S_ID" name="S_ID"
                                class="form-select form-select-sm @error('S_ID') is-invalid @enderror">
                            @foreach ($sections as $s)
                                <option value="{{ $s->S_ID }}"
                                        {{ (int) $val('S_ID', $userSection) === (int) $s->S_ID ? 'selected' : '' }}>
                                    {{ $s->S_CODE }}{{ $s->S_DESCRIPTION ? ' — ' . $s->S_DESCRIPTION : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('S_ID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ── Date/Time slot ───────────────────────────────────────── --}}
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto px-2"
                            style="font-size:var(--font-size-sm); font-weight:600; color:var(--text-muted-soft);">
                        Horaire <span class="text-danger">*</span>
                    </legend>

                    <div class="row g-3">
                        <div class="col-sm-3">
                            <label class="form-label" for="EH_DATE_DEBUT">
                                Date début <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="EH_DATE_DEBUT" name="EH_DATE_DEBUT"
                                   class="form-control form-control-sm @error('EH_DATE_DEBUT') is-invalid @enderror"
                                   value="{{ $horVal('EH_DATE_DEBUT') ? \Carbon\Carbon::parse($horVal('EH_DATE_DEBUT'))->format('Y-m-d') : '' }}"
                                   required>
                            @error('EH_DATE_DEBUT')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label" for="EH_DATE_FIN">Date fin</label>
                            <input type="date" id="EH_DATE_FIN" name="EH_DATE_FIN"
                                   class="form-control form-control-sm @error('EH_DATE_FIN') is-invalid @enderror"
                                   value="{{ $horVal('EH_DATE_FIN') ? \Carbon\Carbon::parse($horVal('EH_DATE_FIN'))->format('Y-m-d') : '' }}">
                            @error('EH_DATE_FIN')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label" for="EH_DEBUT">Heure début</label>
                            <input type="time" id="EH_DEBUT" name="EH_DEBUT"
                                   class="form-control form-control-sm @error('EH_DEBUT') is-invalid @enderror"
                                   value="{{ $horVal('EH_DEBUT') ? substr($horVal('EH_DEBUT'), 0, 5) : '' }}">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label" for="EH_FIN">Heure fin</label>
                            <input type="time" id="EH_FIN" name="EH_FIN"
                                   class="form-control form-control-sm @error('EH_FIN') is-invalid @enderror"
                                   value="{{ $horVal('EH_FIN') ? substr($horVal('EH_FIN'), 0, 5) : '' }}">
                        </div>
                    </div>
                </fieldset>

                {{-- ── Responsable + Personnel requis ──────────────────────── --}}
                <div class="row g-3 mb-3">
                    <div class="col-sm-8">
                        <label class="form-label fw-semibold" for="E_CHEF">Responsable</label>
                        <select id="E_CHEF" name="E_CHEF"
                                class="form-select form-select-sm @error('E_CHEF') is-invalid @enderror">
                            <option value="">— Aucun —</option>
                            @foreach ($chefs as $chef)
                                <option value="{{ $chef->P_ID }}"
                                        {{ (int) $val('E_CHEF') === (int) $chef->P_ID ? 'selected' : '' }}>
                                    {{ strtoupper($chef->P_NOM) }} {{ ucfirst(mb_strtolower($chef->P_PRENOM)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold" for="E_NB">Personnel requis</label>
                        <input type="number" id="E_NB" name="E_NB"
                               class="form-control form-control-sm @error('E_NB') is-invalid @enderror"
                               value="{{ $val('E_NB', 0) }}"
                               min="0" max="9999">
                    </div>
                </div>

                {{-- ── Notes ───────────────────────────────────────────────── --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="E_COMMENT">Commentaire</label>
                    <textarea id="E_COMMENT" name="E_COMMENT" rows="3"
                              class="form-control form-control-sm @error('E_COMMENT') is-invalid @enderror"
                              maxlength="5000">{{ $val('E_COMMENT') }}</textarea>
                </div>

                {{-- ── Flags ───────────────────────────────────────────────── --}}
                <div class="d-flex gap-4 mb-4 flex-wrap">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="E_OPEN_TO_EXT"
                               name="E_OPEN_TO_EXT" value="1"
                               {{ $val('E_OPEN_TO_EXT') ? 'checked' : '' }}>
                        <label class="form-check-label" for="E_OPEN_TO_EXT">
                            Ouvert aux externes
                        </label>
                    </div>
                    @if ($isEdit)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="E_CLOSED"
                                   name="E_CLOSED" value="1"
                                   {{ $val('E_CLOSED') ? 'checked' : '' }}>
                            <label class="form-check-label" for="E_CLOSED">Clôturé</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="E_CANCELED"
                                   name="E_CANCELED" value="1"
                                   {{ $val('E_CANCELED') ? 'checked' : '' }}>
                            <label class="form-check-label text-danger" for="E_CANCELED">
                                Annulé
                            </label>
                        </div>
                    @endif
                </div>

                {{-- ── Actions ──────────────────────────────────────────────── --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>
                        {{ $isEdit ? 'Enregistrer' : 'Créer l\'activité' }}
                    </button>
                    <a href="{{ $isEdit ? route('evenement.show', $event->E_CODE) : route('evenement.index') }}"
                       class="btn btn-outline-secondary btn-sm">
                        Annuler
                    </a>
                </div>

            </form>
        </div>{{-- end widget-card-body --}}
    </div>{{-- end widget-card --}}

    {{-- ── Delete (edit only) ────────────────────────────────────────────────── --}}
    @if ($isEdit && auth()->user()->hasPermission(19))
        <div class="widget-card mt-3 border-danger" style="border-color:var(--bs-danger) !important;">
            <div class="widget-card-header text-danger">
                <div class="widget-card-title">
                    <i class="fas fa-trash me-1"></i> Zone dangereuse
                </div>
            </div>
            <div class="widget-card-body">
                <p class="text-muted small mb-2">
                    La suppression est définitive et retire également les participations, véhicules et matériels associés.
                </p>
                <form method="POST" action="{{ route('evenement.destroy', $event->E_CODE) }}"
                      onsubmit="return confirm('Supprimer définitivement cette activité ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i> Supprimer l'activité
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>

@endsection
