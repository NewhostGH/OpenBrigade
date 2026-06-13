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
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'network' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'network']) }}">
                <i class="fas fa-network-wired me-1"></i> Réseau
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

            <div class="ob-hab-toolbar pb-2">
                <span class="fw-semibold"><i class="fas fa-id-badge me-1 text-secondary"></i> Authentification</span>
                <span class="text-muted" style="font-size:var(--font-size-xs);">TOTP et fédération LDAP / Active Directory.</span>
            </div>

            {{-- TOTP row --}}
            <div class="p-3 rounded border mb-3 d-flex align-items-center gap-3">
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                        <i class="fas fa-mobile-alt me-1 text-success"></i> TOTP / 2FA
                        <span class="ms-1 ob-badge ob-badge-int" style="font-size:10px;">Actif</span>
                    </div>
                    <div class="text-muted" style="font-size:var(--font-size-xs);">
                        Codes à 6 chiffres · récupération · désactivation vérifiée par code.
                        L'obligation 2FA se configure par groupe dans les
                        <a href="{{ route('admin.security', ['tab' => 'passwords']) }}">politiques de mot de passe</a>.
                    </div>
                </div>
            </div>

            {{-- LDAP domains --}}
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold" style="font-size:var(--font-size-sm);">
                    <i class="fas fa-server me-1 text-secondary"></i> Domaines LDAP / Active Directory
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-add-domain"
                        style="font-size:var(--font-size-xs);">
                    <i class="fas fa-plus me-1"></i> Ajouter un domaine
                </button>
            </div>

            @if ($ldapDomains->isEmpty())
            <div class="text-muted p-3 rounded border text-center mb-3" style="font-size:var(--font-size-sm);">
                Aucun domaine configuré. La vérification des mots de passe se fait localement.
            </div>
            @else
            <table class="table table-sm table-hover mb-3">
                <thead>
                    <tr style="font-size:var(--font-size-xs);text-transform:uppercase;color:var(--text-muted);">
                        <th class="ps-2">Nom</th>
                        <th>Hôte</th>
                        <th>Méthode</th>
                        <th>Chiffrement</th>
                        <th>Priorité</th>
                        <th>État</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($ldapDomains as $dom)
                <tr>
                    <td class="ps-2" style="vertical-align:middle;font-size:var(--font-size-sm);">{{ $dom->name }}</td>
                    <td style="vertical-align:middle;font-size:var(--font-size-xs);"><code>{{ $dom->host }}:{{ $dom->port }}</code></td>
                    <td style="vertical-align:middle;font-size:var(--font-size-xs);">
                        {{ $dom->auth_method === 'upn' ? 'UPN' : 'Bind' }}
                    </td>
                    <td style="vertical-align:middle;font-size:var(--font-size-xs);">
                        @if ($dom->use_tls) LDAPS
                        @elseif ($dom->use_starttls) STARTTLS
                        @else <span class="text-danger">Aucun</span>
                        @endif
                    </td>
                    <td style="vertical-align:middle;font-size:var(--font-size-xs);">{{ $dom->priority }}</td>
                    <td style="vertical-align:middle;">
                        @if ($dom->enabled)
                            <span class="ob-badge ob-badge-int" style="font-size:10px;">Actif</span>
                        @else
                            <span class="ob-badge" style="font-size:10px;background:var(--bs-secondary-bg);">Désactivé</span>
                        @endif
                    </td>
                    <td style="vertical-align:middle;" class="pe-2">
                        <div class="d-flex gap-1 justify-content-end">
                            <button type="button" class="btn btn-xs btn-outline-secondary ldap-test-btn"
                                    data-url="{{ route('admin.ldap.test', $dom->id) }}"
                                    data-id="{{ $dom->id }}">
                                <i class="fas fa-plug"></i>
                            </button>
                            <a href="{{ route('admin.ldap.edit', $dom->id) }}" class="btn btn-xs btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.ldap.destroy', $dom->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                        onclick="return confirm('Supprimer ce domaine LDAP et toutes ses règles ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <div id="ldap-test-result-{{ $dom->id }}" class="d-none mt-1" style="font-size:var(--font-size-xs);min-width:160px;"></div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif

            {{-- SSO placeholder --}}
            <div class="p-3 rounded" style="border:1px dashed var(--border-color);opacity:.6;">
                <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                    <i class="fas fa-sign-in-alt me-1"></i> SSO — SAML 2.0 / OAuth
                    <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                </div>
                <div class="text-muted" style="font-size:var(--font-size-xs);">
                    Authentification unique via Keycloak, Azure AD, Google Workspace…
                </div>
            </div>

        </div>

        {{-- Modal: add domain --}}
        <div class="modal fade" id="modal-add-domain" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.ldap.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" style="font-size:var(--font-size-sm);">Nouveau domaine LDAP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="font-size:var(--font-size-sm);">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom affiché</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Corp AD" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold">Hôte</label>
                                <input type="text" name="host" class="form-control form-control-sm" placeholder="ldap.example.com" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold">Port</label>
                                <input type="number" name="port" class="form-control form-control-sm" value="389">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Base DN</label>
                            <input type="text" name="base_dn" class="form-control form-control-sm" placeholder="DC=example,DC=com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Méthode</label>
                            <select name="auth_method" class="form-select form-select-sm">
                                <option value="bind">Bind (recherche DN)</option>
                                <option value="upn">UPN direct (Active Directory)</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="new-domain-enabled" checked>
                            <label class="form-check-label" for="new-domain-enabled">Activé</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-sm btn-primary">Créer et configurer</button>
                    </div>
                </div>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
        document.querySelectorAll('.ldap-test-btn').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const id = btn.dataset.id;
                const result = document.getElementById('ldap-test-result-' + id);
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
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
                    result.className = 'mt-1 p-1 rounded ' + (data.ok ? 'alert alert-success' : 'alert alert-danger');
                    result.innerHTML = (data.ok ? '✓ ' : '✗ ') + data.message;
                } catch (e) {
                    result.className = 'mt-1 p-1 rounded alert alert-danger';
                    result.innerHTML = '✗ Erreur réseau.';
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plug"></i>';
                }
            });
        });
        </script>
        @endpush
        @endif

        {{-- ── Réseau ────────────────────────────────────────────────────────── --}}
        @if ($tab === 'network')
        <div class="p-3">

            <div class="ob-hab-toolbar pb-2">
                <span class="fw-semibold"><i class="fas fa-network-wired me-1 text-secondary"></i> Connectivité réseau</span>
                <span class="text-muted" style="font-size:var(--font-size-xs);">
                    Adresses externes que l'application peut contacter. À communiquer à l'équipe réseau pour les règles pare-feu sortantes.
                </span>
            </div>

            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr style="font-size:var(--font-size-xs);text-transform:uppercase;color:var(--text-muted);">
                        <th class="ps-2">Service</th>
                        <th>Destination</th>
                        <th>Condition</th>
                        <th>Ce qui est envoyé</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                    {{-- HIBP --}}
                    <tr>
                        <td class="ps-2" style="vertical-align:middle;font-size:var(--font-size-sm);">
                            Have I Been Pwned
                            <span class="ob-badge ob-badge-int ms-1" style="font-size:10px;">Actif</span>
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);"><code>api.pwnedpasswords.com:443</code> HTTPS</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">Politique avec liste noire activée, au changement de mot de passe</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">5 premiers caractères du SHA-1 du mot de passe (k-anonymat)</td>
                        <td style="vertical-align:middle;">
                            <button type="button" class="btn btn-xs btn-outline-secondary net-test-btn"
                                    data-url="{{ route('admin.network.test-hibp') }}"
                                    data-result="hibp-result"
                                    style="font-size:var(--font-size-xs);">
                                <i class="fas fa-plug me-1"></i> Tester
                            </button>
                            <div id="hibp-result" class="d-none mt-1" style="font-size:var(--font-size-xs);"></div>
                        </td>
                    </tr>

                    {{-- LDAP domains --}}
                    @foreach ($ldapDomains as $dom)
                    <tr>
                        <td class="ps-2" style="vertical-align:middle;font-size:var(--font-size-sm);">
                            LDAP — {{ $dom->name }}
                            @if ($dom->enabled)
                                <span class="ob-badge ob-badge-int ms-1" style="font-size:10px;">Actif</span>
                            @else
                                <span class="ob-badge ms-1" style="font-size:10px;background:var(--bs-secondary-bg);">Désactivé</span>
                            @endif
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">
                            <code>{{ $dom->host }}:{{ $dom->port }}</code>
                            @if ($dom->use_tls) LDAPS @elseif ($dom->use_starttls) STARTTLS @else <span class="text-danger">non chiffré</span> @endif
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">Chaque tentative de connexion</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">DN/UPN + mot de passe (chiffrés en transit)</td>
                        <td style="vertical-align:middle;">
                            <button type="button" class="btn btn-xs btn-outline-secondary net-test-btn"
                                    data-url="{{ route('admin.ldap.test', $dom->id) }}"
                                    data-result="ldap-net-result-{{ $dom->id }}"
                                    style="font-size:var(--font-size-xs);">
                                <i class="fas fa-plug me-1"></i> Tester
                            </button>
                            <div id="ldap-net-result-{{ $dom->id }}" class="d-none mt-1" style="font-size:var(--font-size-xs);"></div>
                        </td>
                    </tr>
                    @endforeach

                    @if ($ldapDomains->isEmpty())
                    <tr class="text-muted">
                        <td class="ps-2" style="font-size:var(--font-size-sm);">LDAP</td>
                        <td colspan="3" style="font-size:var(--font-size-xs);">Aucun domaine configuré</td>
                        <td></td>
                    </tr>
                    @endif

                    {{-- SMTP --}}
                    <tr class="text-muted">
                        <td class="ps-2" style="vertical-align:middle;font-size:var(--font-size-sm);">
                            SMTP
                            <span class="ob-badge ob-badge-ext ms-1" style="font-size:10px;">Non implémenté</span>
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);"><code>MAIL_HOST:MAIL_PORT</code></td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">Module messagerie (non implémenté)</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">—</td>
                        <td></td>
                    </tr>

                </tbody>
            </table>

            <div class="alert alert-info mt-3 mb-0" style="font-size:var(--font-size-xs);">
                <i class="fas fa-info-circle me-1"></i>
                Pas de CDN, télémétrie ni serveur de mise à jour. Bootstrap et Font Awesome sont servis localement.
            </div>

        </div>

        @push('scripts')
        <script>
        document.querySelectorAll('.net-test-btn').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const resultEl = document.getElementById(btn.dataset.result);
                btn.disabled = true;
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                resultEl.className = 'd-none';
                try {
                    const r = await fetch(btn.dataset.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await r.json();
                    resultEl.className = 'mt-1 p-1 rounded ' + (data.ok ? 'text-success' : 'text-danger');
                    resultEl.innerHTML = (data.ok ? '✓ ' : '✗ ') + data.message;
                } catch (e) {
                    resultEl.className = 'mt-1 p-1 rounded text-danger';
                    resultEl.innerHTML = '✗ Erreur réseau.';
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            });
        });
        </script>
        @endpush
        @endif

    </div>

</div>

@endsection
