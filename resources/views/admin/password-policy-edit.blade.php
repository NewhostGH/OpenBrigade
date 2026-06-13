@extends('layout.app')

@section('title', ($policy ? 'Modifier' : 'Créer') . " une politique — " . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Sécurité', 'url' => route('admin.security', ['tab' => 'passwords'])],
    ['label' => $policy ? 'Modifier la politique' : 'Nouvelle politique'],
]"/>

<div class="mx-3 mt-3">

<form method="POST"
      action="{{ $policy ? route('admin.policy.update', $policy->id) : route('admin.policy.store') }}">
@csrf
@if ($policy)
    @method('PATCH')
@endif

<div class="row g-3">

    {{-- Main fields --}}
    <div class="col-lg-8">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-shield-alt me-1"></i>
                    {{ $policy ? 'Modifier : ' . $policy->name : 'Nouvelle politique de mot de passe' }}
                </div>
            </div>
            <div class="ob-widget-card-body">

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nom de la politique</label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $policy?->name) }}" required maxlength="80">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Length --}}
                <h6 class="text-secondary mb-3"><i class="fas fa-ruler me-1"></i> Longueur</h6>

                <div class="mb-4">
                    <label for="min_length" class="form-label fw-semibold">Longueur minimale</label>
                    <div class="input-group" style="max-width:180px;">
                        <input type="number" id="min_length" name="min_length"
                               class="form-control @error('min_length') is-invalid @enderror"
                               value="{{ old('min_length', $policy?->min_length ?? 12) }}"
                               min="6" max="128" required>
                        <span class="input-group-text">caractères</span>
                    </div>
                    <div class="form-text">
                        NCSC&nbsp;: ≥&nbsp;12 · ANSSI&nbsp;: ≥&nbsp;12 (4 types), ≥&nbsp;14 (3 types), ≥&nbsp;16 (2 types), ≥&nbsp;20 (1 type).
                    </div>
                    @error('min_length') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Complexity --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-sliders-h me-1"></i> Complexité</h6>
                <p class="text-muted small mb-3">
                    Le NCSC déconseille les règles de complexité — privilégiez la longueur.
                    L'ANSSI les accepte en complément d'un minimum de longueur réduit.
                </p>

                @foreach ([
                    ['require_uppercase', 'Majuscules (A–Z)'],
                    ['require_lowercase', 'Minuscules (a–z)'],
                    ['require_digits',    'Chiffres (0–9)'],
                    ['require_special',   'Caractères spéciaux (!@#$%…)'],
                ] as [$field, $label])
                <div class="form-check mb-2">
                    <input type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1"
                           class="form-check-input"
                           {{ old($field, $policy?->{$field} ?? false) ? 'checked' : '' }}>
                    <label for="{{ $field }}" class="form-check-label">{{ $label }}</label>
                </div>
                @endforeach

                <hr>

                {{-- Expiry --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-calendar-times me-1"></i> Expiration</h6>
                <p class="text-muted small mb-3">
                    Le NCSC et l'ANSSI déconseillent le renouvellement forcé — il affaiblit les mots de passe choisis.
                    Réservez l'expiration aux comptes compromis.
                </p>

                <div class="mb-4">
                    <label for="expiry_days" class="form-label fw-semibold">Validité</label>
                    <div class="input-group" style="max-width:200px;">
                        <input type="number" id="expiry_days" name="expiry_days"
                               class="form-control @error('expiry_days') is-invalid @enderror"
                               value="{{ old('expiry_days', $policy?->expiry_days ?? 0) }}"
                               min="0" max="3650">
                        <span class="input-group-text">jours</span>
                    </div>
                    <div class="form-text">0 = pas d'expiration forcée (recommandé NCSC/ANSSI).</div>
                    @error('expiry_days') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Lockout --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-ban me-1"></i> Verrouillage</h6>

                <div class="mb-4">
                    <label for="max_attempts" class="form-label fw-semibold">Tentatives avant verrouillage</label>
                    <div class="input-group" style="max-width:200px;">
                        <input type="number" id="max_attempts" name="max_attempts"
                               class="form-control @error('max_attempts') is-invalid @enderror"
                               value="{{ old('max_attempts', $policy?->max_attempts ?? 10) }}"
                               min="0" max="100">
                        <span class="input-group-text">tentatives</span>
                    </div>
                    <div class="form-text">NCSC&nbsp;: entre 5 et 10 · 0 = pas de verrouillage.</div>
                    @error('max_attempts') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Blocklist --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-list-ul me-1"></i> Liste noire</h6>

                <div class="form-check mb-4">
                    <input type="checkbox" id="blocklist_check" name="blocklist_check" value="1"
                           class="form-check-input"
                           {{ old('blocklist_check', $policy?->blocklist_check ?? true) ? 'checked' : '' }}>
                    <label for="blocklist_check" class="form-check-label fw-semibold">
                        Bloquer les mots de passe courants
                    </label>
                    <div class="form-text ms-0">
                        Vérifie contre la liste des mots de passe les plus fréquents (recommandé NCSC/ANSSI).
                        Complétez avec <code>storage/app/private/blocklist.txt</code> pour des entrées supplémentaires.
                    </div>
                </div>

                <hr>

                {{-- Require 2FA --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-mobile-alt me-1"></i> Double authentification</h6>

                <div class="form-check mb-4">
                    <input type="checkbox" id="require_2fa" name="require_2fa" value="1"
                           class="form-check-input"
                           {{ old('require_2fa', $policy?->require_2fa ?? false) ? 'checked' : '' }}>
                    <label for="require_2fa" class="form-check-label fw-semibold">
                        Exiger l'authentification à deux facteurs (TOTP)
                    </label>
                    <div class="form-text ms-0">
                        Les utilisateurs dont le groupe applique cette politique seront redirigés vers
                        la configuration TOTP à la prochaine connexion s'ils ne l'ont pas encore activée.
                        Recommandé pour les groupes à privilèges élevés (NCSC / ANSSI).
                    </div>
                </div>

                <hr>

                {{-- Default flag --}}
                <div class="form-check mb-4">
                    <input type="checkbox" id="is_default" name="is_default" value="1"
                           class="form-check-input"
                           {{ old('is_default', $policy?->is_default ?? false) ? 'checked' : '' }}>
                    <label for="is_default" class="form-check-label fw-semibold">
                        Politique par défaut
                    </label>
                    <div class="form-text ms-0">
                        Appliquée aux groupes sans politique explicite. Une seule politique peut être définie par défaut.
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.security', ['tab' => 'passwords']) }}" class="btn btn-outline-secondary">
                        Annuler
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- Group assignment sidebar --}}
    <div class="col-lg-4">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-users me-1"></i> Groupes assignés
                </div>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">

                <p class="text-muted mb-3">
                    Sélectionnez les groupes qui utiliseront cette politique.
                    Un groupe non sélectionné héritera de la politique par défaut.
                </p>

                @php $assignedIds = $policy ? $policy->groups->pluck('id')->toArray() : []; @endphp

                @forelse ($groups as $group)
                <div class="form-check mb-1">
                    <input type="checkbox"
                           id="grp_{{ $group->id }}"
                           name="group_ids[]"
                           value="{{ $group->id }}"
                           class="form-check-input"
                           {{ in_array($group->id, old('group_ids', $assignedIds)) ? 'checked' : '' }}>
                    <label for="grp_{{ $group->id }}" class="form-check-label">
                        {{ $group->name }}
                        @if ($group->is_system)
                            <span class="badge bg-secondary ms-1" style="font-size:.65em;">système</span>
                        @endif
                    </label>
                </div>
                @empty
                <p class="text-muted mb-0">Aucun groupe défini.</p>
                @endforelse

            </div>
        </div>

        <div class="ob-widget-card mt-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-info-circle me-1"></i> Références
                </div>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-xs);">
                <p class="text-muted mb-2">
                    <strong>NCSC</strong> — Privilegiez la longueur sur la complexité ; pas de rotation forcée ; liste noire des mots de passe courants ; verrouillage après 5–10 tentatives.
                </p>
                <p class="text-muted mb-0">
                    <strong>ANSSI</strong> — Gradué&nbsp;: ≥12 + 4 types / ≥14 + 3 / ≥16 + 2 / ≥20 + 1. Activer MFA pour les comptes à privilèges.
                </p>
            </div>
        </div>
    </div>

</div>
</form>
</div>

@endsection
