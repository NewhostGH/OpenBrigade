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
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.security.title')],
]"/>

<div class="mx-3 mt-3">

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'passwords' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'passwords']) }}">
                <i class="fas fa-key me-1"></i> {{ __('admin.security.tab_passwords') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'charter' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'charter']) }}">
                <i class="fas fa-file-contract me-1"></i> {{ __('admin.security.tab_charter') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'sessions' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'sessions']) }}">
                <i class="fas fa-clock me-1"></i> {{ __('admin.security.tab_sessions') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'auth' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'auth']) }}">
                <i class="fas fa-id-badge me-1"></i> {{ __('admin.security.tab_auth') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'network' ? 'active' : '' }}"
               href="{{ route('admin.security', ['tab' => 'network']) }}">
                <i class="fas fa-network-wired me-1"></i> {{ __('admin.security.tab_network') }}
            </a>
        </li>
    </ul>

    <div class="border border-top-0 rounded-bottom bg-white">

        {{-- ── Mot de passe ──────────────────────────────────────────────────── --}}
        @if ($tab === 'passwords')
        <div class="ob-hab-toolbar px-3 pt-2 pb-2">
            <span class="fw-semibold"><i class="fas fa-key me-1 text-secondary"></i> {{ __('admin.policy_list.title') }}</span>
            <span class="text-muted" style="font-size:var(--font-size-xs);">
                {{ __('admin.policy_list.hint') }}
            </span>
            <a href="{{ route('admin.policy.create') }}" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fas fa-plus me-1"></i> {{ __('admin.policy.new_policy_btn') }}
            </a>
        </div>

        @if ($policies->isEmpty())
        <div class="px-3 pb-3 text-muted" style="font-size:var(--font-size-sm);">
            {{ __('admin.policy.no_policies') }}
        </div>
        @else
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr style="font-size:var(--font-size-xs);text-transform:uppercase;color:var(--text-muted);">
                    <th class="ps-3">{{ __('admin.policy.col_name') }}</th>
                    <th>{{ __('admin.policy.col_min_length') }}</th>
                    <th>{{ __('admin.policy.col_complexity') }}</th>
                    <th>{{ __('admin.policy.col_expiry') }}</th>
                    <th>{{ __('admin.policy.col_attempts') }}</th>
                    <th>{{ __('admin.policy.col_2fa') }}</th>
                    <th>{{ __('admin.policy.col_groups') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($policies as $pol)
            <tr>
                <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                    {{ $pol->name }}
                    @if ($pol->is_default)
                        <span class="badge bg-primary ms-1" style="font-size:.65em;">{{ __('admin.policy.badge_default') }}</span>
                    @endif
                </td>
                <td style="vertical-align:middle;font-size:var(--font-size-sm);">{{ $pol->min_length }}</td>
                <td style="vertical-align:middle;font-size:var(--font-size-xs);"> {{-- i18n-ignore --}}
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
                                    onclick="return confirm('{{ __('admin.policy.delete_confirm') }}')">
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
            <span class="fw-semibold"><i class="fas fa-file-contract me-1 text-secondary"></i> {{ __('admin.security.charter_title') }}</span>
            <span class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.security.charter_hint') }}</span>
        </div>
        <table class="table table-sm table-hover mb-0">
            <tbody>

                {{-- Active toggle (ID 48) --}}
                @php($s = $settings->get(48))
                <tr>
                    <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                        {{ __('admin.security.charter_enable') }}
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            {{ __('admin.security.charter_enable_hint') }}
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
                        {{ __('admin.security.charter_text_row') }}
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            @if ($charterUpdatedAt)
                                {{ __('admin.security.charter_last_version', ['date' => \Carbon\Carbon::parse($charterUpdatedAt)->format('d/m/Y à H:i')]) }}
                            @else
                                {{ __('admin.security.charter_default_text') }}
                            @endif
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.security.charter') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit me-1"></i> {{ __('common.edit') }}
                            </a>
                            <a href="{{ route('account.charter') }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="fas fa-eye me-1"></i> {{ __('admin.preview') }}
                            </a>
                        </div>
                    </td>
                </tr>

                {{-- Force re-accept --}}
                <tr>
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        {{ __('admin.security.charter_force_row') }}
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            {{ __('admin.security.charter_force_hint') }}
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST" action="{{ route('account.charter.reset') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                onclick="return confirm('{{ __('admin.security.charter_force_confirm') }}')">
                                <i class="fas fa-redo me-1"></i> {{ __('admin.security.charter_force_btn') }}
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
            <span class="fw-semibold"><i class="fas fa-clock me-1 text-secondary"></i> {{ __('admin.security.sessions_title') }}</span>
            <span class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.security.sessions_hint') }}</span>
        </div>
        <table class="table table-sm table-hover mb-0">
            <tbody>

                {{-- Session expiration (ID 49) — WIP --}}
                @php($s = $settings->get(49))
                <tr class="text-muted">
                    <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                        {{ __('admin.security.session_expiry_row') }} <span class="text-muted">{{ __('admin.security.session_expiry_unit') }}</span>
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">{{ __('admin.not_implemented') }}</span>
                        <div style="font-size:var(--font-size-xs);">{{ __('admin.security.session_expiry_hint') }}</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <input type="number" value="{{ $s?->VALUE ?? '30' }}"
                               class="form-control form-control-sm" style="max-width:100px;" disabled>
                    </td>
                </tr>

                {{-- Days audit (ID 34) --}}
                @php($s = $settings->get(34))
                <tr>
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        {{ __('admin.security.audit_days_row') }} <span class="text-muted">{{ __('admin.security.audit_days_unit') }}</span>
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            {{ __('admin.security.audit_days_hint') }}
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
                @php($s = $settings->get(36))
                <tr class="text-muted">
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        {{ __('admin.security.log_days_row') }} <span class="text-muted">{{ __('admin.security.log_days_unit') }}</span>
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">{{ __('admin.not_implemented') }}</span>
                        <div style="font-size:var(--font-size-xs);">{{ __('admin.security.log_days_hint') }}</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <input type="number" value="{{ $s?->VALUE ?? '100' }}"
                               class="form-control form-control-sm" style="max-width:100px;" disabled>
                    </td>
                </tr>

                {{-- Log actions (ID 25) — WIP --}}
                @php($s = $settings->get(25))
                <tr class="text-muted">
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        {{ __('admin.security.log_actions_row') }}
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">{{ __('admin.not_implemented') }}</span>
                        <div style="font-size:var(--font-size-xs);">{{ __('admin.security.log_actions_hint') }}</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   {{ ($s?->VALUE ?? '0') == '1' ? 'checked' : '' }} disabled>
                        </div>
                    </td>
                </tr>

                {{-- Confidential data (ID 33) — WIP --}}
                @php($s = $settings->get(33))
                <tr class="text-muted">
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        {{ __('admin.security.confidential_row') }}
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">{{ __('admin.not_implemented') }}</span>
                        <div style="font-size:var(--font-size-xs);">
                            {{ __('admin.security.confidential_hint') }}
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
                <span class="fw-semibold"><i class="fas fa-id-badge me-1 text-secondary"></i> {{ __('admin.security.auth_title') }}</span>
                <span class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.security.auth_hint') }}</span>
            </div>

            {{-- TOTP row --}}
            <div class="p-3 rounded border mb-3 d-flex align-items-center gap-3">
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-1" style="font-size:var(--font-size-sm);">
                        <i class="fas fa-mobile-alt me-1 text-success"></i> TOTP / 2FA
                        <span class="ms-1 ob-badge ob-badge-int" style="font-size:10px;">{{ __('admin.security.totp_badge') }}</span>
                    </div>
                    <div class="text-muted" style="font-size:var(--font-size-xs);">
                        {{ __('admin.security.totp_hint') }}
                        <a href="{{ route('admin.security', ['tab' => 'passwords']) }}">{{ __('admin.security.totp_policies_link') }}</a>.
                    </div>
                </div>
            </div>

            {{-- LDAP domains --}}
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold" style="font-size:var(--font-size-sm);">
                    <i class="fas fa-server me-1 text-secondary"></i> {{ __('admin.security.ldap_domains_title') }}
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-add-domain"
                        style="font-size:var(--font-size-xs);">
                    <i class="fas fa-plus me-1"></i> {{ __('admin.security.ldap_add_btn') }}
                </button>
            </div>

            @if ($ldapDomains->isEmpty())
            <div class="text-muted p-3 rounded border text-center mb-3" style="font-size:var(--font-size-sm);">
                {{ __('admin.security.ldap_no_domain') }}
            </div>
            @else
            <table class="table table-sm table-hover mb-3">
                <thead>
                    <tr style="font-size:var(--font-size-xs);text-transform:uppercase;color:var(--text-muted);">
                        <th class="ps-2">{{ __('admin.security.col_name') }}</th>
                        <th>{{ __('admin.security.col_host') }}</th>
                        <th>{{ __('admin.security.col_method') }}</th>
                        <th>{{ __('admin.security.col_encryption') }}</th>
                        <th>{{ __('admin.security.col_priority') }}</th>
                        <th>{{ __('admin.security.col_status') }}</th>
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
                    <td style="vertical-align:middle;font-size:var(--font-size-xs);"> {{-- i18n-ignore --}}
                        @if ($dom->use_tls) LDAPS
                        @elseif ($dom->use_starttls) STARTTLS
                        @else <span class="text-danger">{{ __('admin.security.ldap_none_encryption') }}</span>
                        @endif
                    </td>
                    <td style="vertical-align:middle;font-size:var(--font-size-xs);">{{ $dom->priority }}</td>
                    <td style="vertical-align:middle;">
                        @if ($dom->enabled)
                            <span class="ob-badge ob-badge-int" style="font-size:10px;">{{ __('admin.active') }}</span>
                        @else
                            <span class="ob-badge" style="font-size:10px;background:var(--bs-secondary-bg);">{{ __('admin.disabled') }}</span>
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
                                        onclick="return confirm('{{ __('admin.security.ldap_delete_confirm') }}')">
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
                    <i class="fas fa-sign-in-alt me-1"></i> {{ __('admin.security.sso_title') }}
                    <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">{{ __('admin.not_implemented') }}</span>
                </div>
                <div class="text-muted" style="font-size:var(--font-size-xs);">
                    {{ __('admin.security.sso_hint') }}
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
                        <h5 class="modal-title" style="font-size:var(--font-size-sm);">{{ __('admin.security.modal_new_domain') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="font-size:var(--font-size-sm);">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.ldap.display_name') }}</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Corp AD" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold">{{ __('admin.ldap.host') }}</label>
                                <input type="text" name="host" class="form-control form-control-sm" placeholder="ldap.example.com" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold">{{ __('admin.ldap.port') }}</label>
                                <input type="number" name="port" class="form-control form-control-sm" value="389">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.ldap.base_dn') }}</label>
                            <input type="text" name="base_dn" class="form-control form-control-sm" placeholder="DC=example,DC=com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.ldap.auth_method') }}</label>
                            <select name="auth_method" class="form-select form-select-sm">
                                <option value="bind">{{ __('admin.ldap.auth_bind') }}</option>
                                <option value="upn">{{ __('admin.ldap.auth_upn') }}</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="new-domain-enabled" checked>
                            <label class="form-check-label" for="new-domain-enabled">{{ __('admin.security.modal_enabled_label') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('admin.security.modal_create_btn') }}</button>
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
                <span class="fw-semibold"><i class="fas fa-network-wired me-1 text-secondary"></i> {{ __('admin.security.network_title') }}</span>
                <span class="text-muted" style="font-size:var(--font-size-xs);">
                    {{ __('admin.security.network_hint') }}
                </span>
            </div>

            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr style="font-size:var(--font-size-xs);text-transform:uppercase;color:var(--text-muted);">
                        <th class="ps-2">{{ __('admin.security.col_service') }}</th>
                        <th>{{ __('admin.security.col_dest') }}</th>
                        <th>{{ __('admin.security.col_condition') }}</th>
                        <th>{{ __('admin.security.col_sent') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                    {{-- HIBP --}}
                    <tr>
                        <td class="ps-2" style="vertical-align:middle;font-size:var(--font-size-sm);">
                            Have I Been Pwned
                            <span class="ob-badge ob-badge-int ms-1" style="font-size:10px;">{{ __('admin.active') }}</span>
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);"><code>api.pwnedpasswords.com:443</code> HTTPS</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">{{ __('admin.security.hibp_condition') }}</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">{{ __('admin.security.hibp_sent') }}</td>
                        <td style="vertical-align:middle;">
                            <button type="button" class="btn btn-xs btn-outline-secondary net-test-btn"
                                    data-url="{{ route('admin.network.test-hibp') }}"
                                    data-result="hibp-result"
                                    style="font-size:var(--font-size-xs);">
                                <i class="fas fa-plug me-1"></i> {{ __('admin.test') }}
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
                                <span class="ob-badge ob-badge-int ms-1" style="font-size:10px;">{{ __('admin.active') }}</span>
                            @else
                                <span class="ob-badge ms-1" style="font-size:10px;background:var(--bs-secondary-bg);">{{ __('admin.disabled') }}</span>
                            @endif
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">
                            <code>{{ $dom->host }}:{{ $dom->port }}</code> {{-- i18n-ignore --}}
                            @if ($dom->use_tls) LDAPS @elseif ($dom->use_starttls) STARTTLS @else <span class="text-danger">{{ __('admin.security.network_unencrypted') }}</span> @endif
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">{{ __('admin.security.ldap_condition') }}</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">{{ __('admin.security.ldap_sent') }}</td>
                        <td style="vertical-align:middle;">
                            <button type="button" class="btn btn-xs btn-outline-secondary net-test-btn"
                                    data-url="{{ route('admin.ldap.test', $dom->id) }}"
                                    data-result="ldap-net-result-{{ $dom->id }}"
                                    style="font-size:var(--font-size-xs);">
                                <i class="fas fa-plug me-1"></i> {{ __('admin.test') }}
                            </button>
                            <div id="ldap-net-result-{{ $dom->id }}" class="d-none mt-1" style="font-size:var(--font-size-xs);"></div>
                        </td>
                    </tr>
                    @endforeach

                    @if ($ldapDomains->isEmpty())
                    <tr class="text-muted">
                        <td class="ps-2" style="font-size:var(--font-size-sm);">LDAP</td>
                        <td colspan="3" style="font-size:var(--font-size-xs);">{{ __('admin.security.ldap_no_config') }}</td>
                        <td></td>
                    </tr>
                    @endif

                    {{-- SMTP --}}
                    <tr class="text-muted">
                        <td class="ps-2" style="vertical-align:middle;font-size:var(--font-size-sm);">
                            SMTP
                            <span class="ob-badge ob-badge-ext ms-1" style="font-size:10px;">{{ __('admin.not_implemented') }}</span>
                        </td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);"><code>MAIL_HOST:MAIL_PORT</code></td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">{{ __('admin.security.smtp_condition') }}</td>
                        <td style="vertical-align:middle;font-size:var(--font-size-xs);">—</td>
                        <td></td>
                    </tr>

                </tbody>
            </table>

            <div class="alert alert-info mt-3 mb-0" style="font-size:var(--font-size-xs);">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('admin.security.network_no_cdn') }}
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
