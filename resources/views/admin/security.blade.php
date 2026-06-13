@extends('layout.app')

@section('title', 'Sécurité — ' . config('app.name'))

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ob-sec-toggle').forEach(function (el) {
        el.addEventListener('change', function () { this.closest('form').submit(); });
    });
});
</script>
@endpush

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Sécurité'],
]"/>

<div class="mx-3 mt-3">

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'passwords' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'passwords']) }}">
                <i class="fas fa-key me-1"></i> Mot de passe
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'charter' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'charter']) }}">
                <i class="fas fa-file-contract me-1"></i> Charte
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'sessions' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'sessions']) }}">
                <i class="fas fa-clock me-1"></i> Sessions & audit
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'auth' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'auth']) }}">
                <i class="fas fa-id-badge me-1"></i> Authentification
            </a>
        </li>
    </ul>

    <div class="border border-top-0 rounded-bottom bg-white">

        {{-- ── Mot de passe ──────────────────────────────────────────────────── --}}
        @if ($tab === 'passwords')
        <div class="ob-hab-toolbar px-3 pt-2 pb-2">
            <span class="fw-semibold"><i class="fas fa-key me-1 text-secondary"></i> Politiques de mot de passe</span>
            <span class="text-muted" style="font-size:var(--font-size-xs);">
                Longueur, complexité, expiration et verrouillage — assignables par groupe d'habilitation.
            </span>
            <a href="{{ route('admin.policy.create') }}" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fas fa-plus me-1"></i> Nouvelle politique
            </a>
        </div>

        @if ($policies->isEmpty())
        <div class="px-3 pb-3 text-muted" style="font-size:var(--font-size-sm);">
            Aucune politique définie.
        </div>
        @else
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr style="font-size:var(--font-size-xs);text-transform:uppercase;color:var(--text-muted);">
                    <th class="ps-3">Nom</th>
                    <th>Long. min.</th>
                    <th>Complexité</th>
                    <th>Expiration</th>
                    <th>Tentatives</th>
                    <th>2FA</th>
                    <th>Groupes</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($policies as $pol)
            <tr>
                <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                    {{ $pol->name }}
                    @if ($pol->is_default)
                        <span class="badge bg-primary ms-1" style="font-size:.65em;">défaut</span>
                    @endif
                </td>
                <td style="vertical-align:middle;font-size:var(--font-size-sm);">{{ $pol->min_length }}</td>
                <td style="vertical-align:middle;font-size:var(--font-size-xs);">
                    @php
                        $rules = array_filter([
                            $pol->require_uppercase ? 'A–Z' : null,
                            $pol->require_lowercase ? 'a–z' : null,
                            $pol->require_digits    ? '0–9' : null,
                            $pol->require_special   ? '!@#' : null,
                        ]);
                    @endphp
                    {{ $rules ? implode(' · ', $rules) : '—' }}
                </td>
                <td style="vertical-align:middle;font-size:var(--font-size-sm);">
                    {{ $pol->expiry_days > 0 ? $pol->expiry_days . 'j' : '—' }}
                </td>
                <td style="vertical-align:middle;font-size:var(--font-size-sm);">
                    {{ $pol->max_attempts > 0 ? $pol->max_attempts : '∞' }}
                </td>
                <td style="vertical-align:middle;font-size:var(--font-size-sm);">
                    @if ($pol->require_2fa)
                        <i class="fas fa-check text-success"></i>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td style="vertical-align:middle;font-size:var(--font-size-sm);">
                    {{ $pol->groups_count > 0 ? $pol->groups_count : '—' }}
                </td>
                <td style="vertical-align:middle;" class="pe-3">
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="{{ route('admin.policy.edit', $pol->id) }}"
                           class="btn btn-xs btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if (! $pol->is_default)
                        <form method="POST" action="{{ route('admin.policy.destroy', $pol->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger"
                                    onclick="return confirm('Supprimer cette politique ?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
        @endif

        {{-- ── Charte ────────────────────────────────────────────────────────── --}}
        @if ($tab === 'charter')
        <div class="ob-hab-toolbar px-3 pt-2 pb-0">
            <span class="fw-semibold"><i class="fas fa-file-contract me-1 text-secondary"></i> Charte d'utilisation</span>
            <span class="text-muted" style="font-size:var(--font-size-xs);">Activation, personnalisation du texte et gestion des acceptances utilisateurs.</span>
        </div>
        <table class="table table-sm table-hover mb-0">
            <tbody>

                {{-- Active toggle (ID 48) --}}
                @php $s = $settings->get(48); @endphp
                <tr>
                    <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                        Activer la charte d'utilisation
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Bloque l'accès jusqu'à l'acceptation par l'utilisateur.
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST" action="{{ route('admin.settings.save', 48) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="_back" value="security">
                            <input type="hidden" name="_tab" value="charter">
                            <input type="hidden" name="toggle" value="1">
                            <div class="form-check form-switch">
                                <input class="form-check-input ob-sec-toggle" type="checkbox"
                                       name="VALUE" value="1"
                                       {{ ($s?->VALUE ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </form>
                    </td>
                </tr>

                {{-- Charter text --}}
                <tr>
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Texte de la charte
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            @if ($charterUpdatedAt)
                                Dernière version publiée le {{ \Carbon\Carbon::parse($charterUpdatedAt)->format('d/m/Y à H:i') }}.
                            @else
                                Texte par défaut (généré automatiquement).
                            @endif
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.security.charter') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            <a href="{{ route('account.charter') }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="fas fa-eye me-1"></i> Aperçu
                            </a>
                        </div>
                    </td>
                </tr>

                {{-- Force re-accept --}}
                <tr>
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Forcer la réacceptation immédiate
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Efface toutes les acceptances. Les utilisateurs seront bloqués dès leur prochaine visite.
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST" action="{{ route('account.charter.reset') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                onclick="return confirm('Forcer tous les utilisateurs à réaccepter la charte ?')">
                                <i class="fas fa-redo me-1"></i> Forcer
                            </button>
                        </form>
                    </td>
                </tr>

            </tbody>
        </table>
        @endif

        {{-- ── Sessions & audit ─────────────────────────────────────────────── --}}
        @if ($tab === 'sessions')
        <div class="ob-hab-toolbar px-3 pt-2 pb-0">
            <span class="fw-semibold"><i class="fas fa-clock me-1 text-secondary"></i> Sessions & audit</span>
            <span class="text-muted" style="font-size:var(--font-size-xs);">Durée des sessions, conservation des connexions et des journaux, données confidentielles.</span>
        </div>
        <table class="table table-sm table-hover mb-0">
            <tbody>

                {{-- Session expiration (ID 49) — WIP --}}
                @php $s = $settings->get(49); @endphp
                <tr class="text-muted">
                    <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                        Expiration de session <span class="text-muted">(minutes)</span>
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                        <div style="font-size:var(--font-size-xs);">Timeout de session non encore géré par Laravel.</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <input type="number" value="{{ $s?->VALUE ?? '30' }}"
                               class="form-control form-control-sm" style="max-width:100px;" disabled>
                    </td>
                </tr>

                {{-- Days audit (ID 34) --}}
                @php $s = $settings->get(34); @endphp
                <tr>
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Conservation des connexions <span class="text-muted">(jours)</span>
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Durée de visibilité dans "Utilisateurs connectés".
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST" action="{{ route('admin.settings.save', 34) }}" class="d-flex align-items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="_back" value="security">
                            <input type="hidden" name="_tab" value="sessions">
                            <input type="number" name="VALUE" min="1" max="365"
                                   value="{{ $s?->VALUE ?? '100' }}"
                                   class="form-control form-control-sm" style="max-width:100px;">
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                {{-- Days log (ID 36) — WIP --}}
                @php $s = $settings->get(36); @endphp
                <tr class="text-muted">
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Conservation de l'historique des actions <span class="text-muted">(jours)</span>
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                        <div style="font-size:var(--font-size-xs);">Purge automatique du journal non encore active.</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <input type="number" value="{{ $s?->VALUE ?? '100' }}"
                               class="form-control form-control-sm" style="max-width:100px;" disabled>
                    </td>
                </tr>

                {{-- Log actions (ID 25) — WIP --}}
                @php $s = $settings->get(25); @endphp
                <tr class="text-muted">
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Journalisation des actions
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                        <div style="font-size:var(--font-size-xs);">Enregistrement détaillé des actions des utilisateurs.</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   {{ ($s?->VALUE ?? '0') == '1' ? 'checked' : '' }} disabled>
                        </div>
                    </td>
                </tr>

                {{-- Confidential data (ID 33) — WIP --}}
                @php $s = $settings->get(33); @endphp
                <tr class="text-muted">
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Données confidentielles (dossiers médicaux)
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                        <div style="font-size:var(--font-size-xs);">
                            Autoriser l'enregistrement de données médicales sensibles. Implique le respect des obligations RGPD.
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   {{ ($s?->VALUE ?? '0') == '1' ? 'checked' : '' }} disabled>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
        @endif

        {{-- ── Authentification ─────────────────────────────────────────────── --}}
        @if ($tab === 'auth')
        <div class="p-3">

            <div class="ob-hab-toolbar pb-1">
                <span class="fw-semibold"><i class="fas fa-id-badge me-1 text-secondary"></i> Authentification renforcée</span>
                <span class="text-muted" style="font-size:var(--font-size-xs);">2FA, fédération d'identité et message de première connexion. Ces fonctionnalités sont en cours d'implémentation.</span>
            </div>

            {{-- Info connexion (ID 69) — WIP --}}
            @php $s = $settings->get(69); @endphp
            <table class="table table-sm table-hover mb-4">
                <tbody>
                    <tr class="text-muted">
                        <td class="ps-0" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                            Message lors de la première connexion
                            <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                            <div style="font-size:var(--font-size-xs);">Afficher un message d'accueil personnalisé à la première connexion.</div>
                        </td>
                        <td style="vertical-align:middle;">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       {{ ($s?->VALUE ?? '0') == '1' ? 'checked' : '' }} disabled>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="row g-3">

                <div class="col-md-4">
                    <div class="p-3 rounded" style="border:1px dashed var(--border-color); opacity:.6;">
                        <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                            <i class="fas fa-mobile-alt me-1"></i> TOTP / 2FA
                        </div>
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Authentification à deux facteurs par application (Google Authenticator, Authy…).
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded" style="border:1px dashed var(--border-color); opacity:.6;">
                        <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                            <i class="fas fa-server me-1"></i> LDAP / Active Directory
                        </div>
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Authentification déléguée à un annuaire LDAP ou un AD d'entreprise.
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded" style="border:1px dashed var(--border-color); opacity:.6;">
                        <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                            <i class="fas fa-sign-in-alt me-1"></i> SSO — SAML 2.0 / OAuth
                        </div>
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Authentification unique via un fournisseur d'identité (Keycloak, Azure AD…).
                        </div>
                    </div>
                </div>

            </div>

        </div>
        @endif

    </div>

</div>

@endsection
