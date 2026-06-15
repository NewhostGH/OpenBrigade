@extends('layout.app')

@section('title', 'Authentification à deux facteurs — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Mon compte'],
    ['label' => 'Authentification à deux facteurs'],
]"/>

<div class="mx-3 mt-3">
<div class="row g-3">

    {{-- Status + actions --}}
    <div class="col-lg-7">

        @if ($user->hasEnabledTwoFactorAuthentication())
        {{-- ── 2FA is active ──────────────────────────────────────────────── --}}
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

        {{-- Recovery codes --}}
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-key me-1"></i> Codes de récupération
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                    Conservez ces codes dans un endroit sûr. Chaque code est à usage unique et
                    permet de se connecter si vous perdez l'accès à votre application TOTP.
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

        {{-- Disable 2FA --}}
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

        @elseif (! empty($user->two_factor_secret))
        {{-- ── Secret provisioned, not yet confirmed ─────────────────────── --}}
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
                    Clé manuelle&nbsp;:
                    <code class="user-select-all">{{ $secret }}</code>
                </p>

                <form method="POST" action="{{ route('totp.confirm') }}">
                    @csrf
                    <label for="code" class="form-label fw-semibold">Code de confirmation</label>
                    <div class="d-flex gap-2">
                        <input type="text" id="code" name="code"
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
        {{-- ── 2FA not set up ─────────────────────────────────────────────── --}}
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-mobile-alt me-1"></i> Configurer la 2FA
                </div>
                <span class="badge bg-secondary">Inactif</span>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-0" style="font-size:var(--font-size-sm);">
                    La double authentification n'est pas encore configurée. Rechargez cette page pour démarrer la configuration.
                </p>
            </div>
        </div>
        @endif

    </div>

    {{-- Info sidebar --}}
    <div class="col-lg-5">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-info-circle me-1"></i> À propos de la 2FA
                </div>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">
                <p class="text-muted mb-2">
                    L'authentification à deux facteurs (2FA / TOTP) ajoute une couche de protection :
                    même si votre mot de passe est compromis, l'attaquant ne peut pas se connecter
                    sans votre téléphone.
                </p>
                <p class="text-muted mb-2">
                    <strong>Applications compatibles :</strong> Google Authenticator, Microsoft Authenticator,
                    Authy, 2FAS Auth, Bitwarden, ou tout client TOTP (RFC 6238).
                </p>
                <p class="text-muted mb-0">
                    <strong>Codes de récupération :</strong> conservez-les hors ligne. Ils permettent
                    l'accès si vous perdez votre appareil.
                </p>
            </div>
        </div>
    </div>

</div>
</div>

@endsection
