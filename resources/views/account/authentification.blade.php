@extends('layout.app')

@section('title', 'Authentification — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Mon compte'],
    ['label' => 'Authentification'],
]"/>

<div class="mx-3 mt-3">

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                            @php
                                $hasComplexity = ! empty($policy['require_uppercase'])
                                    || ! empty($policy['require_lowercase'])
                                    || ! empty($policy['require_digits'])
                                    || ! empty($policy['require_special']);
                            @endphp
                            @if ($policy['min_length'] > 0 || $hasComplexity)
                                <div class="form-text">
                                    @if ($policy['min_length'] > 0)
                                        Minimum {{ $policy['min_length'] }} caractères.
                                    @endif
                                    @if (! empty($policy['require_uppercase']))
                                        Au moins une majuscule.
                                    @endif
                                    @if (! empty($policy['require_lowercase']))
                                        Au moins une minuscule.
                                    @endif
                                    @if (! empty($policy['require_digits']))
                                        Au moins un chiffre.
                                    @endif
                                    @if (! empty($policy['require_special']))
                                        Au moins un caractère spécial.
                                    @endif
                                </div>
                            @endif
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

@endsection
