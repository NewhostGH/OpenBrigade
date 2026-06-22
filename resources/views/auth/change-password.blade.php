@extends('layout.app')

@section('title', __('auth_views.change_pwd_title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('auth_views.account_breadcrumb')],
    ['label' => __('auth_views.change_pwd_title')],
]"/>

<div class="mx-3 mt-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="ob-widget-card">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title">
                        <i class="fas fa-key me-1"></i>
                        {{ $isExpired ? __('auth_views.change_pwd_renew_title') : __('auth_views.change_pwd_title') }}
                    </div>
                </div>
                <div class="ob-widget-card-body">

                    @if ($isExpired)
                        @if (! $isFirstLogin)
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ __('auth_views.change_pwd_expired_warn') }}
                            </div>
                        @else
                            <div class="alert alert-info mb-3">
                                {{ __('auth_views.change_pwd_first_login') }}
                            </div>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('account.password.update') }}">
                        @csrf

                        @if (! $isFirstLogin)
                            <div class="mb-3">
                                <label for="current" class="form-label">{{ __('auth_views.change_pwd_current') }}</label>
                                <input type="password" id="current" name="current"
                                    class="form-control @error('current') is-invalid @enderror"
                                    autocomplete="current-password" required>
                                @error('current')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="new1" class="form-label">{{ __('auth_views.change_pwd_new') }}</label>
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
                                        {{ __('auth_views.change_pwd_min', ['min' => $policy['min_length']]) }}
                                    @endif
                                    @if (! empty($policy['require_uppercase']))
                                        {{ __('auth_views.change_pwd_uppercase') }}
                                    @endif
                                    @if (! empty($policy['require_lowercase']))
                                        {{ __('auth_views.change_pwd_lowercase') }}
                                    @endif
                                    @if (! empty($policy['require_digits']))
                                        {{ __('auth_views.change_pwd_digits') }}
                                    @endif
                                    @if (! empty($policy['require_special']))
                                        {{ __('auth_views.change_pwd_special') }}
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="new2" class="form-label">{{ __('auth_views.change_pwd_confirm') }}</label>
                            <input type="password" id="new2" name="new2"
                                class="form-control @error('new2') is-invalid @enderror"
                                autocomplete="new-password" required>
                            @error('new2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ __('auth_views.change_pwd_btn') }}
                        </button>
                        @if (! $isExpired)
                            <a href="{{ route('personnel.show', auth()->user()->P_ID) }}"
                               class="btn btn-outline-secondary ms-2">
                                {{ __('auth_views.change_pwd_cancel') }}
                            </a>
                        @endif
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
