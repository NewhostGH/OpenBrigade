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
        @php
            $ldapEnabled   = (bool) config('ldap.enabled');
            $ldapMethod    = config('ldap.auth_method', 'bind');
            $ldapHost      = config('ldap.connections.default.hosts.0', '—');
            $ldapPort      = config('ldap.connections.default.port', 389);
            $ldapBaseDn    = config('ldap.connections.default.base_dn', '—');
            $ldapUsername  = config('ldap.connections.default.username', '—');
            $ldapTls       = config('ldap.connections.default.use_tls', false);
            $ldapStartTls  = config('ldap.connections.default.use_starttls', false);
            $ldapFilter    = config('ldap.user_filter', '');
            $ldapUpnSuffix = config('ldap.upn_suffix', '');
        @endphp
        <div class="p-3">

            <div class="ob-hab-toolbar pb-1">
                <span class="fw-semibold"><i class="fas fa-id-badge me-1 text-secondary"></i> Authentification renforcée</span>
                <span class="text-muted" style="font-size:var(--font-size-xs);">TOTP, fédération LDAP et message de première connexion.</span>
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

                {{-- ── TOTP ──────────────────────────────────────────────────── --}}
                <div class="col-md-6">
                    <div class="p-3 rounded border">
                        <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                            <i class="fas fa-mobile-alt me-1 text-success"></i> TOTP / 2FA
                            <span class="ms-1 ob-badge ob-badge-int" style="font-size:10px;">Actif</span>
                        </div>
                        <div class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                            Authentification à deux facteurs via application TOTP (Google Authenticator, Aegis, Authy…).
                            Chaque utilisateur peut activer le 2FA depuis son profil. L'activation peut être rendue
                            obligatoire par groupe via les <a href="{{ route('admin.security', ['tab' => 'passwords']) }}">politiques de mot de passe</a>.
                        </div>
                        <div style="font-size:var(--font-size-xs);">
                            <i class="fas fa-circle text-success me-1" style="font-size:8px;"></i>
                            Codes à 6 chiffres · codes de récupération · désactivation vérifiée par code TOTP
                        </div>
                    </div>
                </div>

                {{-- ── LDAP ──────────────────────────────────────────────────── --}}
                <div class="col-md-6">
                    <div class="p-3 rounded border {{ $ldapEnabled ? '' : 'border-secondary' }}">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="fw-semibold" style="font-size:var(--font-size-sm);">
                                <i class="fas fa-server me-1 {{ $ldapEnabled ? 'text-success' : 'text-secondary' }}"></i>
                                LDAP / Active Directory
                                @if ($ldapEnabled)
                                    <span class="ms-1 ob-badge ob-badge-int" style="font-size:10px;">Actif</span>
                                @else
                                    <span class="ms-1 ob-badge" style="font-size:10px;background:var(--bs-secondary-bg);">Désactivé</span>
                                @endif
                            </div>
                            @if ($ldapEnabled)
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="ldap-test-btn"
                                    data-url="{{ route('admin.ldap.test') }}"
                                    style="font-size:var(--font-size-xs);">
                                <i class="fas fa-plug me-1"></i> Tester la connexion
                            </button>
                            @endif
                        </div>

                        <div class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                            Délègue la vérification du mot de passe à l'annuaire LDAP.
                            Le compte local doit exister dans <code>pompier</code> — seul le mot de passe
                            est vérifié côté LDAP.
                        </div>

                        @if ($ldapEnabled)
                        <table class="table table-sm mb-2" style="font-size:var(--font-size-xs);">
                            <tbody>
                                <tr><th class="fw-normal text-muted" style="width:38%;">Hôte</th><td><code>{{ $ldapHost }}:{{ $ldapPort }}</code></td></tr>
                                <tr><th class="fw-normal text-muted">Base DN</th><td><code>{{ $ldapBaseDn }}</code></td></tr>
                                <tr><th class="fw-normal text-muted">Compte service</th><td><code>{{ $ldapUsername }}</code></td></tr>
                                <tr><th class="fw-normal text-muted">Méthode</th><td>
                                    @if ($ldapMethod === 'upn')
                                        UPN — suffixe <code>{{ $ldapUpnSuffix ?: '(vide)' }}</code>
                                    @else
                                        Recherche DN — filtre <code>{{ $ldapFilter }}</code>
                                    @endif
                                </td></tr>
                                <tr><th class="fw-normal text-muted">Chiffrement</th><td>
                                    @if ($ldapTls) TLS (LDAPS)
                                    @elseif ($ldapStartTls) STARTTLS
                                    @else <span class="text-danger">Aucun</span>
                                    @endif
                                </td></tr>
                            </tbody>
                        </table>
                        <div id="ldap-test-result" class="d-none" style="font-size:var(--font-size-xs);"></div>
                        @else
                        <div style="font-size:var(--font-size-xs);">
                            Pour activer LDAP, définissez <code>LDAP_ENABLED=true</code> et les variables
                            <code>LDAP_HOST</code>, <code>LDAP_BASE_DN</code>, <code>LDAP_USERNAME</code>,
                            <code>LDAP_PASSWORD</code> dans le fichier <code>.env</code>.
                            Voir <a href="{{ url('/docs/security/ldap.md') }}" target="_blank">la documentation LDAP</a>.
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ── SSO placeholder ───────────────────────────────────────── --}}
                <div class="col-12">
                    <div class="p-3 rounded" style="border:1px dashed var(--border-color); opacity:.6;">
                        <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                            <i class="fas fa-sign-in-alt me-1"></i> SSO — SAML 2.0 / OAuth
                            <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                        </div>
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Authentification unique via un fournisseur d'identité (Keycloak, Azure AD, Google Workspace…).
                        </div>
                    </div>
                </div>

            </div>

        </div>

        @push('scripts')
        <script>
        (function () {
            const btn = document.getElementById('ldap-test-btn');
            if (!btn) return;
            const result = document.getElementById('ldap-test-result');
            btn.addEventListener('click', async function () {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Test…';
                result.className = 'd-none';
                try {
                    const r = await fetch(btn.dataset.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await r.json();
                    result.className = 'p-2 rounded ' + (data.ok ? 'alert alert-success' : 'alert alert-danger');
                    result.innerHTML = (data.ok ? '<i class="fas fa-check me-1"></i>' : '<i class="fas fa-times me-1"></i>') + data.message;
                } catch (e) {
                    result.className = 'p-2 rounded alert alert-danger';
                    result.innerHTML = '<i class="fas fa-times me-1"></i> Erreur réseau.';
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plug me-1"></i> Tester la connexion';
                }
            });
        })();
        </script>
        @endpush
        @endif

    </div>

</div>

@endsection
