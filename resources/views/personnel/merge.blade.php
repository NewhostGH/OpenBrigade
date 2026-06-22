@extends('layout.app')

@section('title', 'Fusion homonymes — ' . strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('personnel.title'), 'url' => route('personnel.index')],
    ['label' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM, 'url' => route('personnel.show', $personnel)],
    ['label' => __('personnel.merge_title')],
]"/>

<div class="mx-3 mt-3">

    @php
        $sameBirthdate = $personnel->P_BIRTHDATE && $doublon->P_BIRTHDATE
            && $personnel->P_BIRTHDATE === $doublon->P_BIRTHDATE;
        $differentBirthdate = $personnel->P_BIRTHDATE && $doublon->P_BIRTHDATE
            && $personnel->P_BIRTHDATE !== $doublon->P_BIRTHDATE;
    @endphp

    {{-- Alert about birthdate comparison --}}
    @if ($sameBirthdate)
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        {{ __('personnel.merge_alert_same') }}
    </div>
    @elseif ($differentBirthdate)
    <div class="alert alert-danger">
        <i class="fas fa-times-circle me-2"></i>
        {!! __('personnel.merge_alert_diff') !!}
    </div>
    @else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ __('personnel.merge_alert_unknown') }}
    </div>
    @endif

    <form method="POST" action="{{ route('personnel.merge', [$personnel, $doublon]) }}">
        @csrf

        <div class="row g-3 mb-4">

            {{-- Main person card --}}
            <div class="col-md-6">
                <div class="ob-widget-card h-100">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-star text-primary me-1"></i>
                            {{ __('personnel.merge_card_principale') }} <span class="badge bg-primary ms-2">N°{{ $personnel->P_ID }}</span>
                        </div>
                        <div class="ob-widget-card-actions">
                            <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-xs btn-light" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:140px;font-size:var(--font-size-sm);">{{ __('personnel.merge_col_nom') }}</td>
                                <td><strong>{{ ucfirst(mb_strtolower($personnel->P_PRENOM)) }} {{ strtoupper($personnel->P_NOM) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_date_naiss') }}</td>
                                <td>{{ $personnel->P_BIRTHDATE ? \Carbon\Carbon::parse($personnel->P_BIRTHDATE)->format('d/m/Y') : '—' }}
                                    @if ($personnel->P_BIRTHPLACE) à {{ $personnel->P_BIRTHPLACE }} @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_statut') }}</td>
                                <td>{{ $personnel->P_STATUT }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_section') }}</td>
                                <td>{{ $mainSection->S_CODE ?? '' }}{{ $mainSection->S_DESCRIPTION ? ' — ' . $mainSection->S_DESCRIPTION : '' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_cree_le') }}</td>
                                <td>{{ $personnel->created_at ? \Carbon\Carbon::parse($personnel->created_at)->format('d/m/Y') : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Doublon person card --}}
            <div class="col-md-6">
                <div class="ob-widget-card h-100 border-warning">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-copy text-warning me-1"></i>
                            {{ __('personnel.merge_card_doublon') }} <span class="badge bg-warning text-dark ms-2">N°{{ $doublon->P_ID }}</span>
                        </div>
                        <div class="ob-widget-card-actions">
                            <a href="{{ route('personnel.show', $doublon) }}" class="btn btn-xs btn-light" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:140px;font-size:var(--font-size-sm);">{{ __('personnel.merge_col_nom') }}</td>
                                <td><strong>{{ ucfirst(mb_strtolower($doublon->P_PRENOM)) }} {{ strtoupper($doublon->P_NOM) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_date_naiss') }}</td>
                                <td>{{ $doublon->P_BIRTHDATE ? \Carbon\Carbon::parse($doublon->P_BIRTHDATE)->format('d/m/Y') : '—' }}
                                    @if ($doublon->P_BIRTHPLACE) à {{ $doublon->P_BIRTHPLACE }} @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_statut') }}</td>
                                <td>{{ $doublon->P_STATUT }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_section') }}</td>
                                <td>{{ $doublonSection->S_CODE ?? '' }}{{ $doublonSection->S_DESCRIPTION ? ' — ' . $doublonSection->S_DESCRIPTION : '' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.merge_col_cree_le') }}</td>
                                <td>{{ $doublon->created_at ? \Carbon\Carbon::parse($doublon->created_at)->format('d/m/Y') : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Merge options --}}
        <div class="ob-widget-card mb-4">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-sliders-h me-1"></i> {{ __('personnel.merge_options_title') }}
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                    {!! __('personnel.merge_options_intro', ['doublon' => $doublon->P_ID, 'principal' => $personnel->P_ID]) !!}
                </p>
                <div class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="competences" id="cbCompetences"
                                   value="1" {{ $nb['competences'] > 0 ? 'checked' : 'disabled' }}>
                            <label class="form-check-label" for="cbCompetences">
                                {{ __('personnel.merge_cb_competences') }}
                                <span class="badge bg-secondary ms-1">{{ $nb['competences'] }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="formations" id="cbFormations"
                                   value="1" {{ $nb['formations'] > 0 ? 'checked' : 'disabled' }}>
                            <label class="form-check-label" for="cbFormations">
                                {{ __('personnel.merge_cb_formations') }}
                                <span class="badge bg-secondary ms-1">{{ $nb['formations'] }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="participations" id="cbParticipations"
                                   value="1" {{ $nb['participations'] > 0 ? 'checked' : 'disabled' }}>
                            <label class="form-check-label" for="cbParticipations">
                                {{ __('personnel.merge_cb_participations') }}
                                <span class="badge bg-secondary ms-1">{{ $nb['participations'] }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="radier" id="cbRadier" value="1" checked>
                            <label class="form-check-label text-warning" for="cbRadier">
                                <i class="fas fa-user-minus me-1"></i>
                                {{ __('personnel.merge_cb_radier', ['id' => $doublon->P_ID]) }}
                            </label>
                        </div>
                    </div>
                    @if (auth()->user()->hasPermission(3))
                    <div class="col-sm-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="supprimer" id="cbSupprimer" value="1">
                            <label class="form-check-label text-danger" for="cbSupprimer">
                                <i class="fas fa-trash me-1"></i>
                                {{ __('personnel.merge_cb_supprimer', ['id' => $doublon->P_ID]) }}
                            </label>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning"
                    onclick="return confirm('{{ __('personnel.merge_confirm', ['principal' => $personnel->P_ID, 'doublon' => $doublon->P_ID]) }}')">
                <i class="fas fa-code-merge me-1"></i> {{ __('personnel.merge_btn') }}
            </button>
            <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> {{ __('common.cancel') }}
            </a>
        </div>

    </form>

</div>

@endsection
