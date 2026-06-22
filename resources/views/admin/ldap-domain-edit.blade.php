@extends('layout.app')

@section('title', 'LDAP — ' . $domain->name . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.security.title'), 'url' => route('admin.security', ['tab' => 'auth'])],
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
                    <i class="fas fa-server me-1"></i> {{ __('admin.ldap.connection_section') }}
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            id="ldap-test-btn"
                            data-url="{{ route('admin.ldap.test', $domain->id) }}"
                            style="font-size:var(--font-size-xs);">
                        <i class="fas fa-plug me-1"></i> {{ __('admin.test') }}
                    </button>
                    @if ($domain->enabled)
                        <span class="ob-badge ob-badge-int" style="font-size:10px;">{{ __('admin.active') }}</span>
                    @else
                        <span class="ob-badge" style="font-size:10px;background:var(--bs-secondary-bg);">{{ __('admin.disabled') }}</span>
                    @endif
                </div>
            </div>
            <div class="ob-widget-card-body">

                <div id="ldap-test-result" class="d-none mb-3" style="font-size:var(--font-size-xs);"></div>

                <form method="POST" action="{{ route('admin.ldap.update', $domain->id) }}">
                @csrf @method('PATCH')

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.display_name') }}</label>
                    <input type="text" name="name" class="form-control form-control-sm"
                           value="{{ old('name', $domain->name) }}" required>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-7">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.host') }}</label>
                        <input type="text" name="host" class="form-control form-control-sm"
                               value="{{ old('host', $domain->host) }}" required>
                    </div>
                    <div class="col-3">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.port') }}</label>
                        <input type="number" name="port" class="form-control form-control-sm"
                               value="{{ old('port', $domain->port) }}" min="1" max="65535">
                    </div>
                    <div class="col-2">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.timeout') }}</label>
                        <input type="number" name="timeout" class="form-control form-control-sm"
                               value="{{ old('timeout', $domain->timeout) }}" min="1" max="60">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.base_dn') }}</label>
                    <input type="text" name="base_dn" class="form-control form-control-sm font-monospace"
                           value="{{ old('base_dn', $domain->base_dn) }}"
                           placeholder="DC=example,DC=com" required>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.service_account') }}</label>
                    <input type="text" name="username" class="form-control form-control-sm font-monospace"
                           value="{{ old('username', $domain->username) }}"
                           placeholder="CN=svc-ldap,OU=Comptes,DC=example,DC=com">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.service_password') }}</label>
                    <input type="password" name="password" class="form-control form-control-sm"
                           placeholder="{{ __('admin.ldap.service_password_ph') }}" autocomplete="new-password">
                    <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.service_password_hint') }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.encryption') }}</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="use_tls" value="1" id="use_tls"
                                   {{ old('use_tls', $domain->use_tls) ? 'checked' : '' }}>
                            <label class="form-check-label" for="use_tls" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.use_tls') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="use_starttls" value="1" id="use_starttls"
                                   {{ old('use_starttls', $domain->use_starttls) ? 'checked' : '' }}>
                            <label class="form-check-label" for="use_starttls" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.use_starttls') }}</label>
                        </div>
                    </div>
                </div>

                <hr style="border-color:var(--border-color);">

                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.auth_method') }}</label>
                    <select name="auth_method" class="form-select form-select-sm" id="auth-method-select">
                        <option value="bind" {{ $domain->auth_method === 'bind' ? 'selected' : '' }}>
                            {{ __('admin.ldap.auth_bind') }}
                        </option>
                        <option value="upn" {{ $domain->auth_method === 'upn' ? 'selected' : '' }}>
                            {{ __('admin.ldap.auth_upn') }}
                        </option>
                    </select>
                </div>

                <div id="bind-fields">
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.user_filter') }}</label>
                        <input type="text" name="user_filter" class="form-control form-control-sm font-monospace"
                               value="{{ old('user_filter', $domain->user_filter) }}"
                               placeholder="(&(objectClass=person)(|(uid={login})(mail={login})))">
                        <div class="text-muted" style="font-size:var(--font-size-xs);">{!! __('admin.ldap.user_filter_hint') !!}</div>
                    </div>
                </div>

                <div id="upn-fields" class="d-none">
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.upn_suffix') }}</label>
                        <input type="text" name="upn_suffix" class="form-control form-control-sm"
                               value="{{ old('upn_suffix', $domain->upn_suffix) }}"
                               placeholder="@corp.example.com">
                        <div class="text-muted" style="font-size:var(--font-size-xs);">{!! __('admin.ldap.upn_result') !!}</div>
                    </div>
                </div>

                <hr style="border-color:var(--border-color);">

                <div class="row g-2 mb-3">
                    <div class="col-auto">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.priority') }}</label>
                        <input type="number" name="priority" class="form-control form-control-sm"
                               value="{{ old('priority', $domain->priority) }}" min="0" max="999" style="max-width:90px;">
                        <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.priority_hint') }}</div>
                    </div>
                </div>

                <div class="d-flex gap-3 flex-wrap mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="domain-enabled"
                               {{ old('enabled', $domain->enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="domain-enabled" style="font-size:var(--font-size-sm);">{{ __('admin.ldap.domain_enabled') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="restrict_to_ou" value="1" id="restrict_to_ou"
                               {{ old('restrict_to_ou', $domain->restrict_to_ou) ? 'checked' : '' }}>
                        <label class="form-check-label" for="restrict_to_ou" style="font-size:var(--font-size-sm);">
                            {{ __('admin.ldap.whitelist_mode') }}
                            <span class="text-muted" style="font-size:var(--font-size-xs);">{!! __('admin.ldap.whitelist_hint') !!}</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-save me-1"></i> {{ __('common.save') }}
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
                    <i class="fas fa-arrows-alt-h me-1"></i> {{ __('admin.ldap.attr_section') }}
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                    {!! __('admin.ldap.attr_hint') !!}
                </p>

                @if ($domain->attributeMaps->isNotEmpty())
                <table class="table table-sm mb-3" style="font-size:var(--font-size-xs);">
                    <thead>
                        <tr style="text-transform:uppercase;color:var(--text-muted);">
                            <th>{{ __('admin.ldap.attr_ldap') }}</th>
                            <th>{{ __('admin.ldap.attr_local') }}</th>
                            <th class="text-center">{{ __('admin.ldap.col_overwrite') }}</th>
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
                    <span class="text-muted fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.quick_fill') }}</span>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @php($quickFills = [
                            'AD standard'   => ['mail' => 'P_EMAIL', 'givenName' => 'P_PRENOM', 'sn' => 'P_NOM', 'sAMAccountName' => 'P_CODE'],
                            'OpenLDAP'      => ['mail' => 'P_EMAIL', 'givenName' => 'P_PRENOM', 'sn' => 'P_NOM', 'uid' => 'P_CODE'],
                        ])
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
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.attr_ldap') }}</label>
                    <input type="text" name="ldap_attr" id="attr-ldap" class="form-control form-control-sm font-monospace"
                           list="ldap-attr-list" required>
                    <datalist id="ldap-attr-list">
                        {{-- Active Directory --}}
                        <option value="sAMAccountName">sAMAccountName — identifiant AD</option> {{-- i18n-ignore --}}
                        <option value="userPrincipalName">userPrincipalName — UPN (user@domain)</option> {{-- i18n-ignore --}}
                        <option value="mail">mail — adresse e-mail principale</option> {{-- i18n-ignore --}}
                        <option value="proxyAddresses">proxyAddresses — adresses e-mail secondaires</option> {{-- i18n-ignore --}}
                        <option value="givenName">givenName — prénom</option> {{-- i18n-ignore --}}
                        <option value="sn">sn — nom de famille</option> {{-- i18n-ignore --}}
                        <option value="displayName">displayName — nom d'affichage</option> {{-- i18n-ignore --}}
                        <option value="cn">cn — nom complet</option> {{-- i18n-ignore --}}
                        <option value="department">department — département / service</option> {{-- i18n-ignore --}}
                        <option value="title">title — intitulé de poste</option> {{-- i18n-ignore --}}
                        <option value="company">company — organisation</option> {{-- i18n-ignore --}}
                        <option value="telephoneNumber">telephoneNumber — téléphone fixe</option> {{-- i18n-ignore --}}
                        <option value="mobile">mobile — téléphone mobile</option> {{-- i18n-ignore --}}
                        <option value="facsimileTelephoneNumber">facsimileTelephoneNumber — fax</option> {{-- i18n-ignore --}}
                        <option value="streetAddress">streetAddress — adresse postale</option> {{-- i18n-ignore --}}
                        <option value="l">l — ville</option> {{-- i18n-ignore --}}
                        <option value="postalCode">postalCode — code postal</option> {{-- i18n-ignore --}}
                        <option value="co">co — pays</option> {{-- i18n-ignore --}}
                        <option value="employeeID">employeeID — matricule employé</option> {{-- i18n-ignore --}}
                        <option value="employeeNumber">employeeNumber — numéro employé</option> {{-- i18n-ignore --}}
                        <option value="extensionAttribute1">extensionAttribute1</option> {{-- i18n-ignore --}}
                        <option value="extensionAttribute2">extensionAttribute2</option> {{-- i18n-ignore --}}
                        <option value="extensionAttribute3">extensionAttribute3</option> {{-- i18n-ignore --}}
                        <option value="memberOf">memberOf — groupes AD</option> {{-- i18n-ignore --}}
                        <option value="manager">manager — responsable hiérarchique (DN)</option> {{-- i18n-ignore --}}
                        <option value="physicalDeliveryOfficeName">physicalDeliveryOfficeName — bureau</option> {{-- i18n-ignore --}}
                        <option value="description">description</option> {{-- i18n-ignore --}}
                        <option value="info">info — notes</option> {{-- i18n-ignore --}}
                        <option value="thumbnailPhoto">thumbnailPhoto — photo</option> {{-- i18n-ignore --}}
                        {{-- OpenLDAP / LDAP standard --}}
                        <option value="uid">uid — identifiant Unix</option> {{-- i18n-ignore --}}
                        <option value="uidNumber">uidNumber — UID numérique</option> {{-- i18n-ignore --}}
                        <option value="gidNumber">gidNumber — GID groupe</option> {{-- i18n-ignore --}}
                        <option value="homeDirectory">homeDirectory — répertoire home</option> {{-- i18n-ignore --}}
                        <option value="loginShell">loginShell — shell</option> {{-- i18n-ignore --}}
                        <option value="gecos">gecos — informations GECOS</option> {{-- i18n-ignore --}}
                        <option value="shadowExpire">shadowExpire — expiration mot de passe</option> {{-- i18n-ignore --}}
                        <option value="shadowLastChange">shadowLastChange — dernière modification MDP</option> {{-- i18n-ignore --}}
                        <option value="ou">ou — unité organisationnelle</option> {{-- i18n-ignore --}}
                        <option value="o">o — organisation</option> {{-- i18n-ignore --}}
                        <option value="labeledURI">labeledURI — URL</option> {{-- i18n-ignore --}}
                    </datalist>
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.attr_local') }}</label>
                    <select name="local_field" id="attr-local" class="form-select form-select-sm" required>
                        @foreach ($localFields as $field => $label)
                        <option value="{{ $field }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-2 d-flex align-items-end pb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="attr-overwrite">
                        <label class="form-check-label" for="attr-overwrite" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.attr_overwrite') }}</label>
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
                    <i class="fas fa-sitemap me-1"></i> {{ __('admin.ldap.ou_section') }}
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-3" style="font-size:var(--font-size-xs);">
                    {!! __('admin.ldap.ou_hint') !!}
                </p>

                @if ($domain->ouRules->isNotEmpty())
                <table class="table table-sm mb-3" style="font-size:var(--font-size-xs);">
                    <thead>
                        <tr style="text-transform:uppercase;color:var(--text-muted);">
                            <th>{{ __('admin.ldap.col_ou_dn') }}</th>
                            <th>{{ __('admin.ldap.col_filter') }}</th>
                            <th>{{ __('admin.ldap.col_action') }}</th>
                            <th>{{ __('admin.ldap.col_group') }}</th>
                            <th>{{ __('admin.ldap.col_role') }}</th>
                            @if ($multiSite) <th>{{ __('admin.ldap.col_section') }}</th> @endif
                            <th>{{ __('admin.ldap.col_priority') }}</th>
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
                                <span class="badge bg-danger">{{ __('admin.ldap.action_deny') }}</span>
                            @elseif ($rule->action === 'allow')
                                <span class="badge bg-success">{{ __('admin.ldap.action_allow') }}</span>
                            @else
                                <span class="badge bg-info text-dark">{{ __('admin.ldap.action_assign') }}</span>
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
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.ou_dn_label') }}</label>
                    <input type="text" name="ou_dn" class="form-control form-control-sm font-monospace"
                           placeholder="OU=Pompiers,DC=example,DC=com" required>
                    <div class="text-muted" style="font-size:var(--font-size-xs);">
                        {{ __('admin.ldap.ou_dn_hint') }}
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">
                        {{ __('admin.ldap.extra_filter_label') }} <span class="text-muted fw-normal">{{ __('admin.ldap.extra_filter_optional') }}</span>
                    </label>
                    <input type="text" name="extra_filter" class="form-control form-control-sm font-monospace"
                           placeholder="(memberOf=CN=Actifs,OU=Groupes,DC=example,DC=com)">
                    <div class="text-muted" style="font-size:var(--font-size-xs);">
                        {{ __('admin.ldap.extra_filter_hint') }}
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-auto">
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.action_label') }}</label>
                        <select name="action" class="form-select form-select-sm" id="ou-action-select">
                            <option value="allow">{{ __('admin.ldap.action_allow') }}</option>
                            <option value="deny">{{ __('admin.ldap.action_deny') }}</option>
                            <option value="assign">{{ __('admin.ldap.action_assign') }}</option>
                        </select>
                    </div>
                    <div class="col" id="ou-assign-fields">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.col_group') }}</label>
                                <select name="group_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach ($groups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.col_role') }}</label>
                                <select name="role_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach ($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($multiSite)
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.col_section') }}</label>
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
                        <label class="form-label fw-semibold" style="font-size:var(--font-size-xs);">{{ __('admin.ldap.priority') }}</label>
                        <input type="number" name="priority" class="form-control form-control-sm" value="10" min="0" max="999" style="width:70px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i> {{ __('admin.ldap.add_rule') }}
                </button>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- Danger zone --}}
<div class="mt-3 p-3 rounded border border-danger" style="background:rgba(220,53,69,.04);">
    <div class="fw-semibold text-danger mb-1" style="font-size:var(--font-size-sm);">
        <i class="fas fa-exclamation-triangle me-1"></i> {{ __('admin.danger_zone') }}
    </div>
    <form method="POST" action="{{ route('admin.ldap.destroy', $domain->id) }}" class="d-inline">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger"
                onclick="return confirm('{{ __('admin.ldap.delete_domain_confirm') }}')">
            <i class="fas fa-trash me-1"></i> {{ __('admin.ldap.delete_domain_btn') }}
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
