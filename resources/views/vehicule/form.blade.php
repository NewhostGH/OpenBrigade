@extends('layout.app')

@section('title', ($vehicule ? 'Modifier' : 'Nouveau véhicule') . ' — ' . config('app.name'))

@section('content')

@php
    $isEdit     = $vehicule !== null;
    $formAction = $isEdit ? route('vehicule.update', $vehicule->V_ID) : route('vehicule.store');

    $val = fn(string $field, $default = null) => old($field, $isEdit ? ($vehicule->$field ?? $default) : $default);

    // Breadcrumb built in PHP — @if cannot live inside a PHP expression
    $breadcrumb = [['label' => 'Véhicules', 'url' => route('vehicule.index')]];
    if ($isEdit) {
        $breadcrumb[] = ['label' => $vehicule->V_IMMATRICULATION ?: $vehicule->V_INDICATIF,
                         'url'   => route('vehicule.show', $vehicule->V_ID)];
        $breadcrumb[] = ['label' => 'Modifier'];
    } else {
        $breadcrumb[] = ['label' => 'Nouveau véhicule'];
    }

    // Date urgency left-border color (edit mode only)
    $dateBorder = function (string $field) use ($vehicule, $isEdit): string {
        if (! $isEdit) return 'var(--component-border)';
        $date = $vehicule->$field ?? null;
        if (! $date) return 'var(--component-border)';
        if ($date->isPast())                    return '#dc2626';
        if ($date->lte(now()->addDays(30)))     return '#d97706';
        return '#16a34a';
    };
    $dateHint = function (string $field) use ($vehicule, $isEdit): string {
        if (! $isEdit) return '';
        $date = $vehicule->$field ?? null;
        if (! $date) return '';
        $diff = (int) now()->diffInDays($date, false);
        if ($diff < 0) return 'Expiré il y a ' . abs($diff) . ' j.';
        return 'Dans ' . $diff . ' j.';
    };
@endphp

<x-ob-breadcrumb :items="$breadcrumb"/>

<div class="mx-3 mt-3">
<div class="ob-widget-card">

    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-{{ $isEdit ? 'edit' : 'plus-circle' }}"></i>
            {{ $isEdit
                ? 'Modifier — ' . ($vehicule->V_IMMATRICULATION ?: $vehicule->V_INDICATIF)
                : 'Nouveau véhicule' }}
        </div>
        @if($isEdit)
            <a href="{{ route('vehicule.show', $vehicule->V_ID) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> Voir la fiche
            </a>
        @endif
    </div>

    <div class="ob-widget-card-body">

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li style="font-size:var(--font-size-sm)">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $formAction }}">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div class="row g-4">

                {{-- ════════════════════════════════════════════════════════
                     LEFT COLUMN — primary fields
                     ════════════════════════════════════════════════════════ --}}
                <div class="col-lg-7">

                    {{-- Section label --}}
                    <p class="ob-form-label">
                        <i class="fas fa-id-card me-1"></i> Identification
                    </p>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-5">
                            <label class="form-label fw-semibold" for="V_IMMATRICULATION">
                                Immatriculation <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="V_IMMATRICULATION" name="V_IMMATRICULATION"
                                   class="form-control form-control-sm @error('V_IMMATRICULATION') is-invalid @enderror"
                                   value="{{ $val('V_IMMATRICULATION', '') }}"
                                   maxlength="20" required autofocus
                                   style="text-transform:uppercase; font-weight:600; letter-spacing:.04em;"
                                   oninput="this.value=this.value.toUpperCase()">
                            @error('V_IMMATRICULATION')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold" for="V_INDICATIF">Indicatif</label>
                            <input type="text" id="V_INDICATIF" name="V_INDICATIF"
                                   class="form-control form-control-sm"
                                   value="{{ $val('V_INDICATIF', '') }}"
                                   maxlength="50">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-semibold" for="V_INVENTAIRE">N° inventaire</label>
                            <input type="text" id="V_INVENTAIRE" name="V_INVENTAIRE"
                                   class="form-control form-control-sm"
                                   value="{{ $val('V_INVENTAIRE', '') }}"
                                   maxlength="50">
                        </div>
                    </div>

                    {{-- Section label --}}
                    <p class="ob-form-label">
                        <i class="fas fa-truck me-1"></i> Caractéristiques
                    </p>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-5">
                            <label class="form-label fw-semibold" for="TV_CODE">Type <span class="text-danger">*</span></label>
                            <select id="TV_CODE" name="TV_CODE" required
                                    class="form-select form-select-sm @error('TV_CODE') is-invalid @enderror">
                                <option value="">— Choisir —</option>
                                @php $currentUsage = null; @endphp
                                @foreach ($types as $t)
                                    @if($t->TV_USAGE !== $currentUsage)
                                        @if($currentUsage !== null) </optgroup> @endif
                                        @if($t->TV_USAGE) <optgroup label="{{ $t->TV_USAGE }}"> @endif
                                        @php $currentUsage = $t->TV_USAGE; @endphp
                                    @endif
                                    <option value="{{ $t->TV_CODE }}"
                                            {{ $val('TV_CODE') === $t->TV_CODE ? 'selected' : '' }}>
                                        {{ $t->TV_CODE }}{{ $t->TV_LIBELLE ? ' — ' . $t->TV_LIBELLE : '' }}
                                    </option>
                                @endforeach
                                @if($currentUsage !== null) </optgroup> @endif
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold" for="V_MODELE">Modèle</label>
                            <input type="text" id="V_MODELE" name="V_MODELE"
                                   class="form-control form-control-sm"
                                   value="{{ $val('V_MODELE', '') }}"
                                   maxlength="50">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-semibold" for="V_ANNEE">Année</label>
                            <input type="number" id="V_ANNEE" name="V_ANNEE"
                                   class="form-control form-control-sm @error('V_ANNEE') is-invalid @enderror"
                                   value="{{ $val('V_ANNEE', '') }}"
                                   min="1900" max="2100" placeholder="{{ date('Y') }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        @feature('multi_site')
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="S_ID">
                                Section <span class="text-danger">*</span>
                            </label>
                            {{-- @error n'est pas compilé dans les attributs de composant — expression liée obligatoire. --}}
                            <x-ob-section-select id="S_ID" name="S_ID" required
                                                 :selected="$val('S_ID', $userSection)"
                                                 :class="$errors->has('S_ID') ? 'is-invalid' : ''" />
                        </div>
                        @else
                            {{-- Multi sites désactivé : la donnée est conservée telle quelle. --}}
                            <input type="hidden" name="S_ID" value="{{ $val('S_ID', $userSection) }}">
                        @endfeature
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="VP_ID">
                                Statut / Position <span class="text-danger">*</span>
                            </label>
                            <select id="VP_ID" name="VP_ID"
                                    class="form-select form-select-sm @error('VP_ID') is-invalid @enderror"
                                    required>
                                <option value="">— Choisir —</option>
                                @foreach ($positions as $p)
                                    @php
                                        $selected = $val('VP_ID') === $p->VP_ID || (!$isEdit && $p->VP_OPERATIONNEL && $loop->first);
                                    @endphp
                                    <option value="{{ $p->VP_ID }}"
                                            {{ $selected ? 'selected' : '' }}>
                                        {{ $p->VP_LIBELLE }}
                                    </option>
                                @endforeach
                            </select>
                            @error('VP_ID')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="V_KM">Kilométrage actuel</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="V_KM" name="V_KM"
                                       class="form-control form-control-sm"
                                       value="{{ $val('V_KM', '') }}"
                                       min="0" placeholder="0">
                                <span class="input-group-text text-muted">km</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="V_KM_REVISION">Km prochaine révision</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="V_KM_REVISION" name="V_KM_REVISION"
                                       class="form-control form-control-sm"
                                       value="{{ $val('V_KM_REVISION', '') }}"
                                       min="0" placeholder="0">
                                <span class="input-group-text text-muted">km</span>
                            </div>
                        </div>
                    </div>

                </div>{{-- /col-lg-7 --}}

                {{-- ════════════════════════════════════════════════════════
                     RIGHT COLUMN — dates, equipment, comment
                     ════════════════════════════════════════════════════════ --}}
                <div class="col-lg-5">

                    {{-- Section label --}}
                    <p class="ob-form-label">
                        <i class="fas fa-calendar-alt me-1"></i> Dates d'expiration
                    </p>

                    @php
                        $dateFields = [
                            ['field' => 'V_ASS_DATE',   'id' => 'V_ASS_DATE',   'label' => 'Assurance',      'icon' => 'fas fa-shield-alt'],
                            ['field' => 'V_CT_DATE',    'id' => 'V_CT_DATE',    'label' => 'Contrôle tech.', 'icon' => 'fas fa-clipboard-check'],
                            ['field' => 'V_REV_DATE',   'id' => 'V_REV_DATE',   'label' => 'Révision',       'icon' => 'fas fa-wrench'],
                            ['field' => 'V_TITRE_DATE', 'id' => 'V_TITRE_DATE', 'label' => "Titre d'accès",  'icon' => 'fas fa-id-card'],
                        ];
                    @endphp

                    <div class="row g-2 mb-4">
                        @foreach($dateFields as $df)
                            @php
                                $border = $dateBorder($df['field']);
                                $hint   = $dateHint($df['field']);
                                $raw    = $val($df['field']);
                                $fmted  = $raw ? \Carbon\Carbon::parse($raw)->format('Y-m-d') : '';
                            @endphp
                            <div class="col-6">
                                <div style="border-left:3px solid {{ $border }}; padding-left:8px; transition:border-color .2s;">
                                    <label class="form-label mb-1" for="{{ $df['id'] }}"
                                           style="font-size:var(--font-size-xs); font-weight:600; color:{{ $border === 'var(--component-border)' ? 'var(--text-muted-soft)' : $border }};">
                                        <i class="{{ $df['icon'] }} me-1"></i>{{ $df['label'] }}
                                    </label>
                                    <input type="date" id="{{ $df['id'] }}" name="{{ $df['id'] }}"
                                           class="form-control form-control-sm"
                                           value="{{ $fmted }}">
                                    @if($hint)
                                        <div style="font-size:var(--font-size-xs); color:{{ $border }}; margin-top:2px;">{{ $hint }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Section label --}}
                    <p class="ob-form-label">
                        <i class="fas fa-cogs me-1"></i> Équipement
                    </p>

                    @php
                        $flags = [
                            ['key' => 'V_FLAG1', 'icon' => 'fas fa-snowflake', 'color' => '#0369a1', 'bg' => '#e0f2fe', 'label' => 'Neige'],
                            ['key' => 'V_FLAG2', 'icon' => 'fas fa-wind',      'color' => '#1d4ed8', 'bg' => '#dbeafe', 'label' => 'Climatisation'],
                            ['key' => 'V_FLAG3', 'icon' => 'fas fa-bullhorn',  'color' => '#92400e', 'bg' => '#fef3c7', 'label' => 'Public Address'],
                            ['key' => 'V_FLAG4', 'icon' => 'fas fa-link',      'color' => '#374151', 'bg' => '#f3f4f6', 'label' => 'Attelage'],
                        ];
                    @endphp

                    <div class="row g-2 mb-3">
                        @foreach($flags as $flag)
                            @php $checked = (bool) $val($flag['key']); @endphp
                            <div class="col-6">
                                <label for="{{ $flag['key'] }}"
                                       class="d-flex align-items-center gap-2 rounded px-3 py-2 w-100"
                                       style="cursor:pointer; border:1px solid {{ $checked ? $flag['color'] : 'var(--component-border)' }};
                                              background:{{ $checked ? $flag['bg'] : 'transparent' }};
                                              transition:border-color .15s, background .15s;
                                              font-size:var(--font-size-sm);">
                                    <input type="checkbox" id="{{ $flag['key'] }}" name="{{ $flag['key'] }}" value="1"
                                           class="form-check-input flex-shrink-0 mb-0 ob-veh-flag-cb"
                                           data-color="{{ $flag['color'] }}" data-bg="{{ $flag['bg'] }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <i class="{{ $flag['icon'] }}" style="color:{{ $flag['color'] }}; width:14px; text-align:center;"></i>
                                    <span>{{ $flag['label'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="d-flex align-items-center gap-2 rounded px-3 py-2"
                               for="V_EXTERNE"
                               style="cursor:pointer; border:1px solid var(--component-border);
                                      font-size:var(--font-size-sm);">
                            <input type="checkbox" id="V_EXTERNE" name="V_EXTERNE" value="1"
                                   class="form-check-input flex-shrink-0 mb-0"
                                   {{ $val('V_EXTERNE') ? 'checked' : '' }}>
                            <i class="fas fa-external-link-alt text-muted" style="width:14px; text-align:center;"></i>
                            <span>Véhicule externe</span>
                        </label>
                    </div>

                    {{-- Section label --}}
                    <p class="ob-form-label">
                        <i class="fas fa-comment-alt me-1"></i> Commentaire
                    </p>

                    <textarea id="V_COMMENT" name="V_COMMENT" rows="4"
                              class="form-control form-control-sm"
                              maxlength="2000"
                              placeholder="Notes libres…">{{ $val('V_COMMENT', '') }}</textarea>

                </div>{{-- /col-lg-5 --}}

            </div>{{-- /row --}}

            {{-- ── Actions ─────────────────────────────────────────────────── --}}
            <div class="d-flex gap-2 mt-4 pt-3" style="border-top:1px solid var(--component-border);">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-save me-1"></i>
                    {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le véhicule' }}
                </button>
                <a href="{{ $isEdit ? route('vehicule.show', $vehicule->V_ID) : route('vehicule.index') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Annuler
                </a>
            </div>

        </form>
    </div>
</div>

{{-- ── Danger zone ─────────────────────────────────────────────────────────── --}}
@if ($isEdit && auth()->user()->hasPermission(19))
    <div class="ob-widget-card mt-3" style="border-color:var(--card-danger-border) !important;">
        <div class="ob-widget-card-header" style="background:var(--card-danger-bg);">
            <div class="ob-widget-card-title" style="color:var(--card-danger-title);">
                <i class="fas fa-trash"></i> Zone dangereuse
            </div>
        </div>
        <div class="ob-widget-card-body d-flex align-items-center gap-4">
            <p class="mb-0 text-muted" style="font-size:var(--font-size-sm); flex:1;">
                Supprime définitivement ce véhicule et toutes ses affectations à des activités.
            </p>
            <form method="POST" action="{{ route('vehicule.destroy', $vehicule->V_ID) }}"
                  onsubmit="return confirm('Supprimer définitivement ce véhicule ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger flex-shrink-0">
                    <i class="fas fa-trash me-1"></i> Supprimer
                </button>
            </form>
        </div>
    </div>
@endif

</div>


@push('scripts')
@vite('resources/js/ob-vehicule-form.js')
@endpush

@endsection
