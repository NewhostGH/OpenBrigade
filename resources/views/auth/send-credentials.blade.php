@extends('layout.app')

@section('title', __('auth_views.creds_page_title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('auth_views.creds_breadcrumb_personnel'), 'url' => route('personnel.index')],
    ['label' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM, 'url' => route('personnel.show', $personnel)],
    ['label' => __('auth_views.creds_page_title')],
]"/>

<div class="mx-3 mt-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="ob-widget-card">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title">
                        <i class="fas fa-key me-1"></i>
                        {{ __('auth_views.creds_card_title', ['name' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM]) }}
                    </div>
                </div>
                <div class="ob-widget-card-body">

                    @if ($mode === null)
                        {{-- Step 1: choose mode --}}
                        <p class="mb-3">
                            {!! __('auth_views.creds_intro', ['name' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM]) !!}
                        </p>

                        <dl class="ob-info-grid mb-4">
                            <div class="ob-info-item">
                                <dt>{{ __('auth_views.creds_field_login') }}</dt>
                                <dd>{{ $personnel->P_CODE ?? '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('auth_views.creds_field_email') }}</dt>
                                <dd>
                                    @if ($personnel->P_EMAIL)
                                        {{ $personnel->P_EMAIL }}
                                    @else
                                        <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> {{ __('auth_views.creds_no_email') }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <form method="POST" action="{{ route('personnel.send-credentials', $personnel) }}">
                            @csrf
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" name="mode" value="manual" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i> {{ __('auth_views.creds_btn_manual') }}
                                    <small class="d-block fw-normal">{{ __('auth_views.creds_btn_manual_sub') }}</small>
                                </button>
                                <button type="submit" name="mode" value="auto" class="btn btn-outline-primary"
                                    @if (! $personnel->P_EMAIL) disabled title="{{ __('auth_views.creds_btn_auto_disabled') }}" @endif>
                                    <i class="fas fa-envelope me-1"></i> {{ __('auth_views.creds_btn_auto') }}
                                    <small class="d-block fw-normal">{{ __('auth_views.creds_btn_auto_sub') }}</small>
                                </button>
                            </div>
                        </form>

                    @elseif ($mode === 'manual')
                        {{-- Step 2: manual result --}}
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ __('auth_views.creds_manual_success') }}
                        </div>

                        <p>{!! __('auth_views.creds_manual_intro', ['name' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM]) !!} :</p>

                        <dl class="ob-info-grid mb-4">
                            <div class="ob-info-item">
                                <dt>{{ __('auth_views.creds_field_login') }}</dt>
                                <dd>
                                    <code class="user-select-all">{{ $personnel->P_CODE ?? '—' }}</code>
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('auth_views.creds_field_tmp_pwd') }}</dt>
                                <dd>
                                    <code class="user-select-all">{{ $newPass }}</code>
                                </dd>
                            </div>
                            @if ($personnel->P_PHONE)
                                <div class="ob-info-item">
                                    <dt>{{ __('auth_views.creds_field_telephone') }}</dt>
                                    <dd><a href="tel:{{ $personnel->P_PHONE }}">{{ $personnel->P_PHONE }}</a></dd>
                                </div>
                            @endif
                            @if ($personnel->P_EMAIL)
                                <div class="ob-info-item">
                                    <dt>{{ __('auth_views.creds_field_email2') }}</dt>
                                    <dd><a href="mailto:{{ $personnel->P_EMAIL }}">{{ $personnel->P_EMAIL }}</a></dd>
                                </div>
                            @endif
                        </dl>

                        <p class="text-muted" style="font-size:var(--font-size-sm);">
                            {{ __('auth_views.creds_expiry_note') }}
                        </p>

                    @elseif ($mode === 'auto')
                        {{-- Step 2: auto result --}}
                        @if ($sent ?? false)
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-envelope me-1"></i>
                                {{ __('auth_views.creds_auto_sent', ['email' => $personnel->P_EMAIL]) }}
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{-- TODO: COMM — wire up NotificationService email sending --}}
                                {{ __('auth_views.creds_auto_unavailable') }}
                            </div>
                            <p>
                                {!! __('auth_views.creds_auto_tmp_label', ['name' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM]) !!}
                            </p>
                            <p><code class="user-select-all fs-5">{{ $newPass }}</code></p>
                        @endif
                    @endif

                    <hr class="mt-4">
                    <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('auth_views.creds_btn_back') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
