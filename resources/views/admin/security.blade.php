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

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="mx-3 mt-3">
<div class="row g-3">

{{-- ── Mot de passe ─────────────────────────────────────────────────────── --}}
<div class="col-12">
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-key me-1"></i> Politique de mot de passe
        </div>
        <span class="ob-badge ob-badge-int">Actif</span>
    </div>
    <div class="ob-widget-card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <tbody>

                {{-- Quality (ID 15) --}}
                @php $s = $settings->get(15); @endphp
                <tr>
                    <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                        Niveau de complexité requis
                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                            Chiffres + lettres (1), + caractère spécial (2)
                        </div>
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST" action="{{ route('admin.settings.save', 15) }}" class="d-flex align-items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="_back" value="security">
                            <select name="VALUE" class="form-select form-select-sm" style="max-width:220px;"
                                    onchange="this.form.submit()">
                                <option value="0" {{ ($s?->VALUE ?? '0') == '0' ? 'selected' : '' }}>0 — Aucune</option>
                                <option value="1" {{ ($s?->VALUE ?? '0') == '1' ? 'selected' : '' }}>1 — Chiffres et lettres</option>
                                <option value="2" {{ ($s?->VALUE ?? '0') == '2' ? 'selected' : '' }}>2 — + Caractère spécial</option>
                            </select>
                        </form>
                    </td>
                </tr>

                {{-- Length (ID 16) --}}
                @php $s = $settings->get(16); @endphp
                <tr>
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Longueur minimale <span class="text-muted">(0 = aucune)</span>
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST" action="{{ route('admin.settings.save', 16) }}" class="d-flex align-items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="_back" value="security">
                            <input type="number" name="VALUE" min="0" max="64"
                                   value="{{ $s?->VALUE ?? '0' }}"
                                   class="form-control form-control-sm" style="max-width:100px;">
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                {{-- Expiry (ID 70) --}}
                @php $s = $settings->get(70); @endphp
                <tr>
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Expiration des mots de passe <span class="text-muted">(jours, 0 = désactivé)</span>
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST" action="{{ route('admin.settings.save', 70) }}" class="d-flex align-items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="_back" value="security">
                            <input type="number" name="VALUE" min="0" max="3650"
                                   value="{{ $s?->VALUE ?? '0' }}"
                                   class="form-control form-control-sm" style="max-width:100px;">
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                {{-- Failure (ID 17) — WIP --}}
                @php $s = $settings->get(17); @endphp
                <tr class="text-muted">
                    <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                        Blocage du compte après X tentatives
                        <span class="ms-1 ob-badge ob-badge-ext" style="font-size:10px;">Non implémenté</span>
                        <div style="font-size:var(--font-size-xs);">Le verrouillage automatique n'est pas encore actif.</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <input type="number" value="{{ $s?->VALUE ?? '5' }}"
                               class="form-control form-control-sm" style="max-width:100px;" disabled>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
</div>

{{-- ── Charte d'utilisation ─────────────────────────────────────────────── --}}
<div class="col-12">
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-file-contract me-1"></i> Charte d'utilisation
        </div>
        <span class="ob-badge ob-badge-int">Actif</span>
    </div>
    <div class="ob-widget-card-body p-0">
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
                            <input type="hidden" name="toggle" value="1">
                            <div class="form-check form-switch">
                                <input class="form-check-input ob-sec-toggle" type="checkbox"
                                       name="VALUE" value="1"
                                       {{ ($s?->VALUE ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </form>
                    </td>
                </tr>

                {{-- Charter text editor link --}}
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
                    <td style="vertical-align:middle;" class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.security.charter') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit me-1"></i> Modifier le texte
                        </a>
                        <a href="{{ route('account.charter') }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                            <i class="fas fa-eye me-1"></i> Aperçu
                        </a>
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
                                <i class="fas fa-redo me-1"></i> Forcer la réacceptation
                            </button>
                        </form>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
</div>

{{-- ── Sessions et audit ────────────────────────────────────────────────── --}}
<div class="col-12">
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-clock me-1"></i> Sessions et audit
        </div>
    </div>
    <div class="ob-widget-card-body p-0">
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

            </tbody>
        </table>
    </div>
</div>
</div>

{{-- ── Authentification renforcée — WIP ───────────────────────────────── --}}
<div class="col-12">
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-id-badge me-1"></i> Authentification renforcée
        </div>
        <span class="ob-badge ob-badge-ext">Non implémenté</span>
    </div>
    <div class="ob-widget-card-body">
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
</div>
</div>

</div>{{-- /.row --}}
</div>{{-- /.mx-3 --}}

@endsection
