@extends('layout.app')

@section('title', __('admin.notifications.title') . ' — ' . config('app.name'))

@section('content')

<script>
    // Toggles save on flip, like the settings page.
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('ob-setting-row-toggle') || e.target.classList.contains('ob-setting-row-select')) {
            e.target.closest('form').submit();
        }
    });
</script>

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.notifications.title')],
]"/>

<div class="mx-3 mt-3 row g-3">
    {{-- ── Email ──────────────────────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-envelope me-1"></i> {{ __('admin.notifications.email_title') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle">
                    <tbody>
                        @include('admin.partials.setting-toggle', [
                            's' => $rows->get('mail_allowed'),
                            'label' => 'admin.notifications.mail_allowed',
                            'hint' => 'admin.notifications.mail_allowed_hint',
                            'default' => '1',
                            'back' => 'notifications',
                        ])
                        @php($mailer = $rows->get('mail_mailer'))
                        <tr>
                            <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                                {{ __('admin.notifications.mail_mailer') }}
                                <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.notifications.mail_mailer_hint') }}</div>
                            </td>
                            <td style="vertical-align:middle;">
                                @if ($mailer)
                                    <form method="POST" action="{{ route('admin.settings.save', $mailer->ID) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="_back" value="notifications">
                                        <select name="VALUE" class="form-select form-select-sm ob-setting-row-select" style="max-width:280px;">
                                            <option value="" @selected(($mailer->VALUE ?? '') === '')>{{ __('admin.notifications.mail_mailer_env') }}</option>
                                            <option value="smtp" @selected($mailer->VALUE === 'smtp')>SMTP</option>
                                            <option value="log" @selected($mailer->VALUE === 'log')>{{ __('admin.notifications.mail_mailer_log') }}</option>
                                            <option value="failover" @selected($mailer->VALUE === 'failover')>{{ __('admin.notifications.mail_mailer_failover') }}</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('mail_host'),
                            'label' => 'admin.notifications.mail_host',
                            'hint' => null,
                            'back' => 'notifications',
                        ])
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('mail_port'),
                            'label' => 'admin.notifications.mail_port',
                            'hint' => null,
                            'back' => 'notifications',
                        ])
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('mail_username'),
                            'label' => 'admin.notifications.mail_username',
                            'hint' => null,
                            'back' => 'notifications',
                        ])
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('mail_password'),
                            'label' => 'admin.notifications.mail_password',
                            'hint' => null,
                            'back' => 'notifications',
                            'type' => 'password',
                        ])
                        @php($scheme = $rows->get('mail_scheme'))
                        <tr>
                            <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                                {{ __('admin.notifications.mail_scheme') }}
                                <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.notifications.mail_scheme_hint') }}</div>
                            </td>
                            <td style="vertical-align:middle;">
                                @if ($scheme)
                                    <form method="POST" action="{{ route('admin.settings.save', $scheme->ID) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="_back" value="notifications">
                                        <select name="VALUE" class="form-select form-select-sm ob-setting-row-select" style="max-width:280px;">
                                            <option value="" @selected(($scheme->VALUE ?? '') === '')>{{ __('admin.notifications.mail_scheme_auto') }}</option>
                                            <option value="smtp" @selected($scheme->VALUE === 'smtp')>{{ __('admin.notifications.mail_scheme_smtp') }}</option>
                                            <option value="smtps" @selected($scheme->VALUE === 'smtps')>{{ __('admin.notifications.mail_scheme_smtps') }}</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('mail_from_address'),
                            'label' => 'admin.notifications.mail_from_address',
                            'hint' => null,
                            'back' => 'notifications',
                        ])
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('mail_from_name'),
                            'label' => 'admin.notifications.mail_from_name',
                            'hint' => null,
                            'back' => 'notifications',
                        ])
                    </tbody>
                </table>
                <div class="text-muted p-3" style="font-size:var(--font-size-xs);">
                    {{ __('admin.notifications.email_transport_note') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── SMS ────────────────────────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-comment-sms me-1"></i> {{ __('admin.notifications.sms_title') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle">
                    <tbody>
                        @php($provider = $rows->get('sms_provider'))
                        <tr>
                            <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
                                {{ __('admin.notifications.sms_provider') }}
                                <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.notifications.sms_provider_hint', ['driver' => $envDriver]) }}</div>
                            </td>
                            <td style="vertical-align:middle;">
                                @if ($provider)
                                    <form method="POST" action="{{ route('admin.settings.save', $provider->ID) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="_back" value="notifications">
                                        <select name="VALUE" class="form-select form-select-sm ob-setting-row-select" style="max-width:280px;">
                                            <option value="" @selected(($provider->VALUE ?? '') === '')>{{ __('admin.notifications.provider_env', ['driver' => $envDriver]) }}</option>
                                            <option value="log" @selected($provider->VALUE === 'log')>{{ __('admin.notifications.provider_log') }}</option>
                                            <option value="null" @selected($provider->VALUE === 'null')>{{ __('admin.notifications.provider_null') }}</option>
                                            <option value="smsgatewayme" @selected(in_array($provider->VALUE, ['smsgatewayme', 'smsgateway.me', 'smsgateway'], true))>SMSGateway.me</option> {{-- i18n-ignore --}}
                                            @if (! in_array($provider->VALUE, ['', 'log', 'null', 'smsgatewayme', 'smsgateway.me', 'smsgateway'], true))
                                                <option value="{{ $provider->VALUE }}" selected>{{ __('admin.notifications.provider_unknown', ['value' => $provider->VALUE]) }}</option>
                                            @endif
                                        </select>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('sms_user'),
                            'label' => 'admin.notifications.sms_user',
                            'hint' => 'admin.notifications.sms_user_hint',
                            'back' => 'notifications',
                        ])
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('sms_password'),
                            'label' => 'admin.notifications.sms_password',
                            'hint' => 'admin.notifications.sms_password_hint',
                            'back' => 'notifications',
                            'type' => 'password',
                        ])
                        @include('admin.partials.setting-text', [
                            's' => $rows->get('sms_api_id'),
                            'label' => 'admin.notifications.sms_api_id',
                            'hint' => 'admin.notifications.sms_api_id_hint',
                            'back' => 'notifications',
                        ])
                    </tbody>
                </table>
                <div class="text-muted p-3" style="font-size:var(--font-size-xs);">
                    {{ __('admin.notifications.sms_fallback_note') }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
