@extends('layout.app')

@section('title', __('account.title_totp') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('account.breadcrumb_account')],
    ['label' => __('account.breadcrumb_totp')],
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
                    <i class="fas fa-shield-alt me-1 text-success"></i> {{ __('account.2fa_active_title') }}
                </div>
                <span class="badge bg-success">{{ __('account.badge_active') }}</span>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">
                <p class="mb-0 text-muted">
                    {{ __('account.2fa_active_desc') }}
                </p>
            </div>
        </div>

        {{-- Recovery codes --}}
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-key me-1"></i> {{ __('account.recovery_title') }}
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                    {{ __('account.recovery_desc') }}
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
                    {{ __('account.recovery_hidden') }}
                </p>
                @endif

                <form method="POST" action="{{ route('totp.codes.regenerate') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                            onclick="return confirm('{{ __('account.confirm_regenerate') }}')">
                        <i class="fas fa-sync-alt me-1"></i> {{ __('account.btn_regenerate') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Disable 2FA --}}
        <div class="ob-widget-card border-danger-subtle">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title text-danger">
                    <i class="fas fa-times-circle me-1"></i> {{ __('account.disable_title') }}
                </div>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                    {{ __('account.disable_desc') }}
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
                                onclick="return confirm('{{ __('account.confirm_disable') }}')">
                            {{ __('account.btn_disable') }}
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
                    <i class="fas fa-mobile-alt me-1"></i> {{ __('account.setup_pending_title') }}
                </div>
                <span class="badge bg-warning text-dark">{{ __('account.badge_pending') }}</span>
            </div>
            <div class="ob-widget-card-body">

                <p class="mb-3" style="font-size:var(--font-size-sm);">
                    {{ __('account.setup_qr_desc') }}
                </p>

                <div class="text-center mb-3">
                    {!! $qrSvg !!}
                </div>

                <p class="text-muted text-center mb-4" style="font-size:var(--font-size-xs);">
                    {{ __('account.manual_key_label_nbsp') }}
                    <code class="user-select-all">{{ $secret }}</code>
                </p>

                <form method="POST" action="{{ route('totp.confirm') }}">
                    @csrf
                    <label for="code" class="form-label fw-semibold">{{ __('account.label_confirm_code') }}</label>
                    <div class="d-flex gap-2">
                        <input type="text" id="code" name="code"
                               class="form-control font-monospace @error('code') is-invalid @enderror"
                               inputmode="numeric" maxlength="6" placeholder="000000"
                               autocomplete="one-time-code" autofocus
                               style="max-width:160px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> {{ __('account.btn_confirm') }}
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
                    <i class="fas fa-mobile-alt me-1"></i> {{ __('account.setup_title') }}
                </div>
                <span class="badge bg-secondary">{{ __('account.badge_inactive') }}</span>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted mb-0" style="font-size:var(--font-size-sm);">
                    {{ __('account.setup_not_configured') }}
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
                    <i class="fas fa-info-circle me-1"></i> {{ __('account.about_title') }}
                </div>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">
                <p class="text-muted mb-2">
                    {{ __('account.about_desc_totp') }}
                </p>
                <p class="text-muted mb-2">
                    <strong>{{ __('account.about_apps') }}</strong> {{ __('account.about_apps_list') }}
                </p>
                <p class="text-muted mb-0">
                    <strong>{{ __('account.about_recovery') }}</strong> {{ __('account.about_recovery_desc') }}
                </p>
            </div>
        </div>
    </div>

</div>
</div>

@endsection
