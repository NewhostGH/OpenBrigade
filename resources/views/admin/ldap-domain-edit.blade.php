@extends('layout.app')

@section('title', 'LDAP — ' . $domain->name . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Sécurité', 'url' => route('admin.security', ['tab' => 'auth'])],
    ['label' => $domain->name],
]"/>

<div class="mx-3 mt-3">

@if ($errors->any())
<div class="alert alert-danger" style="font-size:var(--font-size-sm);">
    @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
</div>
@endif

<div class="row g-3">

    {{-- ── Connection settings ───────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-server me-1"></i> Connexion
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            id="ldap-test-btn"
                            data-url="{{ route('admin.ldap.test', $domain->id) }}"
                            style="font-size:var(--font-size-xs);">
                        <i class="fas fa-plug me-1"></i> Tester
                    </button>
                    @if ($domain->enabled)
                        <span class="ob-badge ob-badge-int" style="font-size:10px;">Actif</span>
                    @else
                        <span class="ob-badge" style="font-size:10px;background:var(--bs-secondary-bg);">Désactivé</span>
                    @endif
                </div>
            </div>
            <div class="ob-widget-card-body">

                <div id="ldap-test-result" class="d-none mb-3" style="font-size:var(--font-size-xs);"></div>

                <form method="POST" action="{{ route('admin.ldap.update', $domain->id) }}">
                @csrf @method('PATCH')

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Nom affiché</label>
                    <input type="text" name="name" class="form-control form-control-sm"
                           value="{{ old('name', $domain->name) }}" required>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-7">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Hôte</label>
                        <input type="text" name="host" class="form-control form-control-sm"
                               value="{{ old('host', $domain->host) }}" required>
                    </div>
                    <div class="col-3">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Port</label>
                        <input type="number" name="port" class="form-control form-control-sm"
                               value="{{ old('port', $domain->port) }}" min="1" max="65535">
                    </div>
                    <div class="col-2">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Timeout</label>
                        <input type="number" name="timeout" class="form-control form-control-sm"
                               value="{{ old('timeout', $domain->timeout) }}" min="1" max="60">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Base DN</label>
                    <input type="text" name="base_dn" class="form-control form-control-sm font-monospace"
                           value="{{ old('base_dn', $domain->base_dn) }}"
                           placeholder="DC=example,DC=com" required>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Compte de service (DN)</label>
                    <input type="text" name="username" class="form-control form-control-sm font-monospace"
                           value="{{ old('username', $domain->username) }}"
                           placeholder="CN=svc-ldap,OU=Comptes,DC=example,DC=com">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Mot de passe du compte de service</label>
                    <input type="password" name="password" class="form-control form-control-sm"
                           placeholder="Laisser vide pour conserver" autocomplete="new-password">
                    <div class="text-muted" style="font-size:var(--font-size-xs);">Stocké chiffré.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Chiffrement</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="use_tls" value="1" id="use_tls"
                                   {{ old('use_tls', $domain->use_tls) ? 'checked' : '' }}>
                            <label class="form-check-label" for="use_tls" style="font-size:var(--font-size-sm);">LDAPS (TLS)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="use_starttls" value="1" id="use_starttls"
                                   {{ old('use_starttls', $domain->use_starttls) ? 'checked' : '' }}>
                            <label class="form-check-label" for="use_starttls" style="font-size:var(--font-size-sm);">STARTTLS</label>
                        </div>
                    </div>
                </div>

                <hr style="border-color:var(--border-color);">

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Méthode d'authentification</label>
                    <select name="auth_method" class="form-select form-select-sm" id="auth-method-select">
                        <option value="bind" {{ $domain->auth_method === 'bind' ? 'selected' : '' }}>
                            Bind — recherche du DN via compte de service
                        </option>
                        <option value="upn" {{ $domain->auth_method === 'upn' ? 'selected' : '' }}>
                            UPN direct — Active Directory
                        </option>
                    </select>
                </div>

                <div id="bind-fields">
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Filtre utilisateur</label>
                        <input type="text" name="user_filter" class="form-control form-control-sm font-monospace"
                               value="{{ old('user_filter', $domain->user_filter) }}"
                               placeholder="(&(objectClass=person)(|(uid={login})(mail={login})))">
                        <div class="text-muted" style="font-size:var(--font-size-xs);"><code>{login}</code> est remplacé par l'identifiant saisi.</div>
                    </div>
                </div>

                <div id="upn-fields" class="d-none">
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Suffixe UPN</label>
                        <input type="text" name="upn_suffix" class="form-control form-control-sm"
                               value="{{ old('upn_suffix', $domain->upn_suffix) }}"
                               placeholder="@corp.example.com">
                        <div class="text-muted" style="font-size:var(--font-size-xs);">Résultat : <code>identifiant@corp.example.com</code></div>
                    </div>
                </div>

                <hr style="border-color:var(--border-color);">

                <div class="row g-2 mb-3">
                    <div class="col-auto">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">Priorité</label>
                        <input type="number" name="priority" class="form-control form-control-sm"
                               value="{{ old('priority', $domain->priority) }}" min="0" max="999" style="max-width:90px;">
                        <div class="text-muted" style="font-size:var(--font-size-xs);">Plus petit = essayé en premier.</div>
                    </div>
                </div>

                <div class="d-flex gap-3 flex-wrap mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="domain-enabled"
                               {{ old('enabled', $domain->enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="domain-enabled" style="font-size:var(--font-size-sm);">Domaine actif</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="restrict_to_ou" value="1" id="restrict_to_ou"
                               {{ old('restrict_to_ou', $domain->restrict_to_ou) ? 'checked' : '' }}>
                        <label class="form-check-label" for="restrict_to_ou" style="font-size:var(--font-size-sm);">
                            Mode liste blanche OU
                            <span class="text-muted" style="font-size:var(--font-size-xs);">(seules les règles <em>Autoriser</em> passent)</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-save me-1"></i> Enregistrer
                </button>

                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">

        {{-- ── Attribute mappings ────────────────────────────────────────── --}}
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-arrows-alt-h me-1"></i> Correspondances d'attributs
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                    Après connexion, les attributs LDAP listés ci-dessous sont copiés vers les champs locaux du compte pompier.
                    <em>Écraser</em> remplace une valeur existante ; sinon seul un champ vide est renseigné.
                </p>

                @if ($domain->attributeMaps->isNotEmpty())
                <table class="table table-sm mb-3" style="font-size:var(--font-size-xs);">
                    <thead>
                        <tr style="text-transform:uppercase;color:var(--text-muted);">
                            <th>Attribut LDAP</th>
                            <th>Champ local</th>
                            <th class="text-center">Écraser</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($domain->attributeMaps as $map)
                    <tr>
                        <td style="vertical-align:middle;"><code>{{ $map->ldap_attr }}</code></td>
                        <td style="vertical-align:middle;"><code>{{ $localFields[$map->local_field] ?? $map->local_field }}</code></td>
                        <td class="text-center" style="vertical-align:middle;">
                            @if ($map->overwrite) <i class="fas fa-check text-warning"></i> @else <span class="text-muted">—</span> @endif
                        </td>
                        <td style="vertical-align:middle;">
                            <form method="POST" action="{{ route('admin.ldap.attr.destroy', [$domain->id, $map->id]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif

                {{-- Quick-fill templates --}}
                <div class="mb-2">
                    <span class="text-muted fw-semibold" style="font-size:var(--font-size-xs);">Suggestions rapides :</span>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @php
                        $quickFills = [
                            'AD standard'   => ['mail' => 'P_EMAIL', 'givenName' => 'P_PRENOM', 'sn' => 'P_NOM', 'sAMAccountName' => 'P_CODE'],
                            'OpenLDAP'      => ['mail' => 'P_EMAIL', 'givenName' => 'P_PRENOM', 'sn' => 'P_NOM', 'uid' => 'P_CODE'],
                        ];
                        @endphp
                        @foreach ($quickFills as $label => $pairs)
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-fill-btn"
                                data-pairs="{{ json_encode($pairs) }}"
                                style="font-size:var(--font-size-xs);">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.ldap.attr.store', $domain->id) }}" class="row g-2 align-items-end" id="attr-form">
                @csrf
                <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">Attribut LDAP</label>
                    <input type="text" name="ldap_attr" id="attr-ldap" class="form-control form-control-sm font-monospace"
                           list="ldap-attr-list" required>
                    <datalist id="ldap-attr-list">
                        {{-- Active Directory --}}
                        <option value="sAMAccountName">sAMAccountName — identifiant AD</option>
                        <option value="userPrincipalName">userPrincipalName — UPN (user@domain)</option>
                        <option value="mail">mail — adresse e-mail principale</option>
                        <option value="proxyAddresses">proxyAddresses — adresses e-mail secondaires</option>
                        <option value="givenName">givenName — prénom</option>
                        <option value="sn">sn — nom de famille</option>
                        <option value="displayName">displayName — nom d'affichage</option>
                        <option value="cn">cn — nom complet</option>
                        <option value="department">department — département / service</option>
                        <option value="title">title — intitulé de poste</option>
                        <option value="company">company — organisation</option>
                        <option value="telephoneNumber">telephoneNumber — téléphone fixe</option>
                        <option value="mobile">mobile — téléphone mobile</option>
                        <option value="facsimileTelephoneNumber">facsimileTelephoneNumber — fax</option>
                        <option value="streetAddress">streetAddress — adresse postale</option>
                        <option value="l">l — ville</option>
                        <option value="postalCode">postalCode — code postal</option>
                        <option value="co">co — pays</option>
                        <option value="employeeID">employeeID — matricule employé</option>
                        <option value="employeeNumber">employeeNumber — numéro employé</option>
                        <option value="extensionAttribute1">extensionAttribute1</option>
                        <option value="extensionAttribute2">extensionAttribute2</option>
                        <option value="extensionAttribute3">extensionAttribute3</option>
                        <option value="memberOf">memberOf — groupes AD</option>
                        <option value="manager">manager — responsable hiérarchique (DN)</option>
                        <option value="physicalDeliveryOfficeName">physicalDeliveryOfficeName — bureau</option>
                        <option value="description">description</option>
                        <option value="info">info — notes</option>
                        <option value="thumbnailPhoto">thumbnailPhoto — photo</option>
                        {{-- OpenLDAP / LDAP standard --}}
                        <option value="uid">uid — identifiant Unix</option>
                        <option value="uidNumber">uidNumber — UID numérique</option>
                        <option value="gidNumber">gidNumber — GID groupe</option>
                        <option value="homeDirectory">homeDirectory — répertoire home</option>
                        <option value="loginShell">loginShell — shell</option>
                        <option value="gecos">gecos — informations GECOS</option>
                        <option value="shadowExpire">shadowExpire — expiration mot de passe</option>
                        <option value="shadowLastChange">shadowLastChange — dernière modification MDP</option>
                        <option value="ou">ou — unité organisationnelle</option>
                        <option value="o">o — organisation</option>
                        <option value="labeledURI">labeledURI — URL</option>
                    </datalist>
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">Champ local</label>
                    <select name="local_field" id="attr-local" class="form-select form-select-sm" required>
                        @foreach ($localFields as $field => $label)
                        <option value="{{ $field }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-2 d-flex align-items-end pb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="attr-overwrite">
                        <label class="form-check-label" for="attr-overwrite" style="font-size:var(--font-size-xs);">Écraser</label>
                    </div>
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                </form>
            </div>
        </div>

        {{-- ── OU rules ──────────────────────────────────────────────────── --}}
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-sitemap me-1"></i> Règles par OU / filtre
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-3" style="font-size:var(--font-size-xs);">
                    <strong>Refuser</strong> bloque la connexion. <strong>Autoriser</strong> est requis en mode liste blanche.
                    <strong>Assigner</strong> applique le groupe, le rôle ou la section après connexion.
                    Les règles de refus l'emportent toujours, quelle que soit la priorité.
                </p>

                @if ($domain->ouRules->isNotEmpty())
                <table class="table table-sm mb-3" style="font-size:var(--font-size-xs);">
                    <thead>
                        <tr style="text-transform:uppercase;color:var(--text-muted);">
                            <th>OU DN</th>
                            <th>Filtre</th>
                            <th>Action</th>
                            <th>Groupe</th>
                            <th>Rôle</th>
                            @if ($multiSite) <th>Section</th> @endif
                            <th>Pri.</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($domain->ouRules as $rule)
                    <tr>
                        <td style="vertical-align:middle;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <code title="{{ $rule->ou_dn }}">{{ $rule->ou_dn }}</code>
                        </td>
                        <td style="vertical-align:middle;">
                            @if ($rule->extra_filter)
                                <code title="{{ $rule->extra_filter }}">…</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">
                            @if ($rule->action === 'deny')
                                <span class="badge bg-danger">Refuser</span>
                            @elseif ($rule->action === 'allow')
                                <span class="badge bg-success">Autoriser</span>
                            @else
                                <span class="badge bg-info text-dark">Assigner</span>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">{{ $rule->group?->name ?? '—' }}</td>
                        <td style="vertical-align:middle;">{{ $rule->role?->name ?? '—' }}</td>
                        @if ($multiSite)
                        <td style="vertical-align:middle;">{{ $rule->section?->S_CODE ?? '—' }}</td>
                        @endif
                        <td style="vertical-align:middle;">{{ $rule->priority }}</td>
                        <td style="vertical-align:middle;">
                            <form method="POST" action="{{ route('admin.ldap.ou.destroy', [$domain->id, $rule->id]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif

                {{-- Add rule form --}}
                <form method="POST" action="{{ route('admin.ldap.ou.store', $domain->id) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">OU DN</label>
                    <input type="text" name="ou_dn" class="form-control form-control-sm font-monospace"
                           placeholder="OU=Pompiers,DC=example,DC=com" required>
                    <div class="text-muted" style="font-size:var(--font-size-xs);">
                        Correspond à l'OU cible et toutes ses OU parentes dans le DN de l'utilisateur.
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">
                        Filtre LDAP supplémentaire <span class="text-muted fw-normal">(optionnel)</span>
                    </label>
                    <input type="text" name="extra_filter" class="form-control form-control-sm font-monospace"
                           placeholder="(memberOf=CN=Actifs,OU=Groupes,DC=example,DC=com)">
                    <div class="text-muted" style="font-size:var(--font-size-xs);">
                        Filtre LDAP appliqué au DN de l'utilisateur pour affiner la règle.
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-auto">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">Action</label>
                        <select name="action" class="form-select form-select-sm" id="ou-action-select">
                            <option value="allow">Autoriser</option>
                            <option value="deny">Refuser</option>
                            <option value="assign">Assigner</option>
                        </select>
                    </div>
                    <div class="col" id="ou-assign-fields">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">Groupe</label>
                                <select name="group_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach ($groups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">Rôle</label>
                                <select name="role_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach ($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($multiSite)
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">Section</label>
                                <select name="section_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach ($sections as $s)
                                    <option value="{{ $s->S_ID }}">{{ $s->S_CODE }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">Priorité</label>
                        <input type="number" name="priority" class="form-control form-control-sm" value="10" min="0" max="999" style="width:70px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i> Ajouter la règle
                </button>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- Danger zone --}}
<div class="mt-3 p-3 rounded border border-danger" style="background:rgba(220,53,69,.04);">
    <div class="fw-semibold text-danger mb-1" style="font-size:var(--font-size-sm);">
        <i class="fas fa-exclamation-triangle me-1"></i> Zone dangereuse
    </div>
    <form method="POST" action="{{ route('admin.ldap.destroy', $domain->id) }}" class="d-inline">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger"
                onclick="return confirm('Supprimer définitivement ce domaine et toutes ses règles ?')">
            <i class="fas fa-trash me-1"></i> Supprimer ce domaine
        </button>
    </form>
</div>

</div>

@push('scripts')
<script>
(function () {

    // Test connection
    const testBtn = document.getElementById('ldap-test-btn');
    const testResult = document.getElementById('ldap-test-result');
    if (testBtn) {
        testBtn.addEventListener('click', async function () {
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Test…';
            testResult.className = 'd-none';
            try {
                const r = await fetch(testBtn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await r.json();
                testResult.className = 'mb-3 p-2 rounded alert ' + (data.ok ? 'alert-success' : 'alert-danger');
                testResult.innerHTML = (data.ok ? '<i class="fas fa-check me-1"></i>' : '<i class="fas fa-times me-1"></i>') + data.message;
            } catch (e) {
                testResult.className = 'mb-3 p-2 rounded alert alert-danger';
                testResult.innerHTML = '<i class="fas fa-times me-1"></i> Erreur réseau.';
            } finally {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fas fa-plug me-1"></i> Tester';
            }
        });
    }

    // Bind vs UPN field toggle
    const methodSelect = document.getElementById('auth-method-select');
    function updateMethodFields() {
        const isUpn = methodSelect.value === 'upn';
        document.getElementById('upn-fields').classList.toggle('d-none', !isUpn);
        document.getElementById('bind-fields').classList.toggle('d-none', isUpn);
    }
    if (methodSelect) {
        methodSelect.addEventListener('change', updateMethodFields);
        updateMethodFields();
    }

    // OU action toggle: only show assign fields when action=assign
    const ouAction = document.getElementById('ou-action-select');
    const assignFields = document.getElementById('ou-assign-fields');
    function updateOuFields() {
        if (assignFields) assignFields.style.display = ouAction.value === 'assign' ? '' : 'none';
    }
    if (ouAction) {
        ouAction.addEventListener('change', updateOuFields);
        updateOuFields();
    }

    // Quick-fill attribute templates: submit each pair individually via fetch
    document.querySelectorAll('.quick-fill-btn').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const pairs = JSON.parse(btn.dataset.pairs);
            const form = document.getElementById('attr-form');
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const url = form.action;

            btn.disabled = true;
            for (const [ldap, local] of Object.entries(pairs)) {
                const body = new URLSearchParams({
                    _token: token, ldap_attr: ldap, local_field: local, overwrite: '0',
                });
                await fetch(url, { method: 'POST', body, headers: { 'Accept': 'text/html' } });
            }
            window.location.reload();
        });
    });

})();
</script>
@endpush

@endsection
