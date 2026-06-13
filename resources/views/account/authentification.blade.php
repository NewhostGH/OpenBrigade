@extends('layout.app')

@section('title', 'Authentification — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Mon compte'],
    ['label' => 'Authentification'],
]"/>

<div class="mx-3 mt-3">

    <ul class="nav nav-tabs mb-0" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'password' ? 'active' : '' }}"
               href="{{ route('account.auth', ['tab' => 'password']) }}">
                <i class="fas fa-key me-1"></i> Mot de passe
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === '2fa' ? 'active' : '' }}"
               href="{{ route('account.auth', ['tab' => '2fa']) }}">
                <i class="fas fa-mobile-alt me-1"></i> Double authentification
                @if ($user->hasEnabledTwoFactorAuthentication())
                    <span class="badge bg-success ms-1" style="font-size:.65em;">Actif</span>
                @elseif ($require2fa)
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.65em;">Requis</span>
                @endif
            </a>
        </li>
    </ul>

    <div class="border border-top-0 rounded-bottom bg-white p-4">

        {{-- ── Mot de passe ──────────────────────────────────────────────────── --}}
        @if ($tab === 'password')

            @if ($isExpired)
                @if (! $isFirstLogin)
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Vous utilisez un mot de passe expiré ou temporaire — vous devez en choisir un nouveau maintenant.
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        Bienvenue ! Veuillez choisir un mot de passe personnel.
                    </div>
                @endif
            @endif

            <div class="row justify-content-start">
                <div class="col-md-7 col-lg-5">

                    <form method="POST" action="{{ route('account.password.update') }}">
                        @csrf

                        @if (! $isFirstLogin)
                            <div class="mb-3">
                                <label for="current" class="form-label">Mot de passe actuel</label>
                                <input type="password" id="current" name="current"
                                    class="form-control @error('current') is-invalid @enderror"
                                    autocomplete="current-password" required>
                                @error('current')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="new1" class="form-label">Nouveau mot de passe</label>
                            <input type="password" id="new1" name="new1"
                                class="form-control @error('new1') is-invalid @enderror"
                                autocomplete="new-password" required
                                @if ($policy['min_length'] > 0) minlength="{{ $policy['min_length'] }}" @endif>
                            @error('new1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            {{-- Strength meter (populated by JS) --}}
                            <div id="pw-meter" class="mt-2 d-none">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div id="pw-bar" class="progress-bar" role="progressbar"
                                             style="width:0%;transition:width .2s,background-color .2s;"></div>
                                    </div>
                                    <small id="pw-label" class="text-muted" style="min-width:70px;font-size:var(--font-size-xs);"></small>
                                </div>
                                <ul id="pw-criteria" class="list-unstyled mb-0" style="font-size:var(--font-size-xs);columns:2;gap:.5rem;"></ul>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="new2" class="form-label">Confirmation</label>
                            <input type="password" id="new2" name="new2"
                                class="form-control @error('new2') is-invalid @enderror"
                                autocomplete="new-password" required>
                            @error('new2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        @if (! $isExpired)
                            <a href="{{ route('personnel.show', auth()->user()->P_ID) }}"
                               class="btn btn-outline-secondary ms-2">
                                Annuler
                            </a>
                        @endif
                    </form>

                </div>
            </div>

        {{-- ── Double authentification ───────────────────────────────────────── --}}
        @else

            <div class="row g-4">
                <div class="col-lg-7">

                    @if ($user->hasEnabledTwoFactorAuthentication())
                    {{-- ── 2FA active ─────────────────────────────────────── --}}
                    <div class="ob-widget-card mb-3">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-shield-alt me-1 text-success"></i> 2FA activée
                            </div>
                            <span class="badge bg-success">Actif</span>
                        </div>
                        <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">
                            <p class="mb-0 text-muted">
                                Votre compte est protégé par l'authentification à deux facteurs.
                                Un code TOTP sera demandé à chaque connexion.
                            </p>
                        </div>
                    </div>

                    <div class="ob-widget-card mb-3">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-key me-1"></i> Codes de récupération
                            </div>
                        </div>
                        <div class="ob-widget-card-body">
                            <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                                Conservez ces codes dans un endroit sûr. Chaque code est à usage unique
                                et permet de se connecter si vous perdez l'accès à votre application TOTP.
                            </p>
                            @if (! empty($recoveryCodes))
                            <div class="p-3 rounded mb-3"
                                 style="background:var(--bg-subtle,#f8f9fa);font-family:monospace;font-size:var(--font-size-sm);">
                                @foreach ($recoveryCodes as $rc)
                                <div>{{ $rc }}</div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted" style="font-size:var(--font-size-sm);">
                                Les codes de récupération ne sont affichés qu'une seule fois après leur génération.
                                Régénérez-les ci-dessous si vous les avez perdus.
                            </p>
                            @endif
                            <form method="POST" action="{{ route('totp.codes.regenerate') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                        onclick="return confirm('Régénérer les codes ? Les anciens codes seront invalides.')">
                                    <i class="fas fa-sync-alt me-1"></i> Régénérer les codes
                                </button>
                            </form>
                        </div>
                    </div>

                    @if (! $require2fa)
                    <div class="ob-widget-card border-danger-subtle">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title text-danger">
                                <i class="fas fa-times-circle me-1"></i> Désactiver la 2FA
                            </div>
                        </div>
                        <div class="ob-widget-card-body">
                            <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                                Saisissez votre code TOTP actuel pour confirmer la désactivation.
                            </p>
                            <form method="POST" action="{{ route('totp.disable') }}">
                                @csrf @method('DELETE')
                                <div class="d-flex gap-2 align-items-start">
                                    <div>
                                        <input type="text" name="code"
                                               class="form-control form-control-sm font-monospace @error('code') is-invalid @enderror"
                                               inputmode="numeric" maxlength="6" placeholder="000000"
                                               style="width:120px;" autocomplete="one-time-code">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Désactiver la protection 2FA ?')">
                                        Désactiver
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info" style="font-size:var(--font-size-sm);">
                        <i class="fas fa-info-circle me-1"></i>
                        La double authentification est requise par votre groupe. Vous ne pouvez pas la désactiver.
                    </div>
                    @endif

                    @elseif (! empty($user->two_factor_secret))
                    {{-- ── Secret provisioned, awaiting confirmation ──────── --}}
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-mobile-alt me-1"></i> Configuration de la 2FA
                            </div>
                            <span class="badge bg-warning text-dark">En attente de confirmation</span>
                        </div>
                        <div class="ob-widget-card-body">
                            <p class="mb-3" style="font-size:var(--font-size-sm);">
                                Scannez ce QR code avec votre application d'authentification
                                (Google Authenticator, Authy, 2FAS…), puis saisissez le code généré pour confirmer.
                            </p>
                            <div class="text-center mb-3">
                                {!! $qrSvg !!}
                            </div>
                            <p class="text-muted text-center mb-4" style="font-size:var(--font-size-xs);">
                                Clé manuelle : <code class="user-select-all">{{ $secret }}</code>
                            </p>
                            <form method="POST" action="{{ route('totp.confirm') }}">
                                @csrf
                                <label for="totp_code" class="form-label fw-semibold">Code de confirmation</label>
                                <div class="d-flex gap-2">
                                    <input type="text" id="totp_code" name="code"
                                           class="form-control font-monospace @error('code') is-invalid @enderror"
                                           inputmode="numeric" maxlength="6" placeholder="000000"
                                           autocomplete="one-time-code" autofocus
                                           style="max-width:160px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check me-1"></i> Confirmer
                                    </button>
                                </div>
                                @error('code')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </form>
                        </div>
                    </div>

                    @else
                    {{-- ── Not set up ──────────────────────────────────────── --}}
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-mobile-alt me-1"></i> Configurer la 2FA
                            </div>
                            <span class="badge bg-secondary">Inactif</span>
                        </div>
                        <div class="ob-widget-card-body">
                            <p class="text-muted mb-0" style="font-size:var(--font-size-sm);">
                                La double authentification n'est pas encore configurée.
                                Rechargez cette page pour démarrer la configuration.
                            </p>
                        </div>
                    </div>
                    @endif

                </div>

                <div class="col-lg-5">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-info-circle me-1"></i> À propos de la 2FA
                            </div>
                        </div>
                        <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">
                            <p class="text-muted mb-2">
                                La double authentification (2FA / TOTP) ajoute une couche de protection :
                                même si votre mot de passe est compromis, l'attaquant ne peut pas se connecter
                                sans votre téléphone.
                            </p>
                            <p class="text-muted mb-2">
                                <strong>Applications compatibles :</strong> Google Authenticator, Microsoft Authenticator,
                                Authy, 2FAS Auth, Bitwarden, ou tout client TOTP (RFC 6238).
                            </p>
                            <p class="text-muted mb-0">
                                <strong>Codes de récupération :</strong> conservez-les hors ligne.
                                Ils permettent l'accès si vous perdez votre appareil.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        @endif

    </div>

</div>

@push('scripts')
@if ($tab === 'password')
<script>
(function () {
    const policy = @json([
        'minLength'        => $policy['min_length'],
        'requireUppercase' => !empty($policy['require_uppercase']),
        'requireLowercase' => !empty($policy['require_lowercase']),
        'requireDigits'    => !empty($policy['require_digits']),
        'requireSpecial'   => !empty($policy['require_special']),
        'blocklist'        => !empty($policy['blocklist_check']),
    ]);

    const input   = document.getElementById('new1');
    const meter   = document.getElementById('pw-meter');
    const bar     = document.getElementById('pw-bar');
    const label   = document.getElementById('pw-label');
    const ulCrit  = document.getElementById('pw-criteria');

    if (!input || !meter) return;

    function isConsecutive(s) {
        if (s.length < 5) return false;
        const step = s.charCodeAt(1) - s.charCodeAt(0);
        if (Math.abs(step) !== 1) return false;
        for (let i = 2; i < s.length; i++) {
            if (s.charCodeAt(i) - s.charCodeAt(i - 1) !== step) return false;
        }
        return true;
    }

    function buildCriteria(pw) {
        const lower = pw.toLowerCase();
        const items = [];

        if (policy.minLength > 0) {
            items.push({
                label: `Au moins ${policy.minLength} caractère${policy.minLength > 1 ? 's' : ''}`,
                pass: pw.length >= policy.minLength,
            });
        }
        if (policy.requireUppercase) {
            items.push({ label: 'Au moins une majuscule', pass: /[A-Z]/.test(pw) });
        }
        if (policy.requireLowercase) {
            items.push({ label: 'Au moins une minuscule', pass: /[a-z]/.test(pw) });
        }
        if (policy.requireDigits) {
            items.push({ label: 'Au moins un chiffre', pass: /[0-9]/.test(pw) });
        }
        if (policy.requireSpecial) {
            items.push({ label: 'Au moins un caractère spécial', pass: /[\W_]/.test(pw) });
        }
        if (policy.blocklist) {
            const trivial = /^(.)\1+$/.test(pw) || isConsecutive(lower);
            items.push({ label: 'Pas de séquence triviale', pass: pw.length > 0 && !trivial });
        }

        return items;
    }

    const levels = [
        null,
        { label: 'Très faible', color: '#dc3545' },
        { label: 'Faible',      color: '#fd7e14' },
        { label: 'Moyen',       color: '#ffc107' },
        { label: 'Fort',        color: '#198754' },
    ];

    function computeLevel(pw, criteria) {
        if (pw.length === 0) return 0;
        const required = criteria.length;
        const passed   = criteria.filter(c => c.pass).length;
        if (required === 0) return pw.length >= 12 ? 4 : pw.length >= 8 ? 3 : 2;
        const ratio = passed / required;
        // Extra bonus for length well beyond minimum
        const longBonus = pw.length >= (policy.minLength || 12) + 8 ? 1 : 0;
        if (ratio < 0.34) return 1;
        if (ratio < 0.67) return 2;
        if (ratio < 1.00) return 3;
        return Math.min(4, 3 + longBonus);
    }

    function render(pw) {
        const criteria = buildCriteria(pw);
        const level    = computeLevel(pw, criteria);

        if (pw.length === 0) {
            meter.classList.add('d-none');
            return;
        }
        meter.classList.remove('d-none');

        // Bar
        const pct = level * 25;
        bar.style.width = pct + '%';
        bar.style.backgroundColor = levels[level]?.color ?? '#ccc';

        // Label
        label.textContent = levels[level]?.label ?? '';
        label.style.color = levels[level]?.color ?? 'inherit';

        // Criteria list
        ulCrit.innerHTML = criteria.map(c => `
            <li class="d-flex align-items-center gap-1 mb-1">
                <i class="fas ${c.pass ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'}"
                   style="font-size:11px;width:12px;"></i>
                <span style="color:${c.pass ? 'inherit' : 'var(--bs-danger)'};">${c.label}</span>
            </li>
        `).join('');
    }

    input.addEventListener('input', () => render(input.value));

    // Render on page load if value is pre-filled (e.g. browser autofill)
    if (input.value) render(input.value);
})();
</script>
@endif
@endpush

@endsection
