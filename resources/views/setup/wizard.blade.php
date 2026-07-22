<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('setup.wizard_title') }} — {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="ob-login-body">
<noscript>
    <div class="ob-noscript">{{ __('layout.noscript') }}</div>
</noscript>

<div class="container py-5" style="max-width:640px;">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
             style="max-height:64px;" onerror="this.style.display='none'">
    </div>

    <div class="ob-widget-card p-4 p-md-5">
        <h1 class="h4 mb-1">{{ __('setup.wizard_heading') }}</h1>
        <p class="text-muted" style="font-size:var(--font-size-sm);">{{ __('setup.wizard_intro') }}</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setup.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="mb-3">
                <label for="type_organisation" class="form-label">{{ __('setup.org_type') }} <span class="text-danger">*</span></label>
                <select id="type_organisation" name="type_organisation" class="form-select" required>
                    <option value="">{{ __('setup.org_type_choose') }}</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}" @selected((string) old('type_organisation', $orgType) === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('setup.org_type_help') }}</div>
            </div>

            <div class="mb-3">
                <label for="cisname" class="form-label">{{ __('setup.cisname') }} <span class="text-danger">*</span></label>
                <input id="cisname" name="cisname" type="text" maxlength="25" required
                       class="form-control" value="{{ old('cisname', $identity['cisname']) }}">
                <div class="form-text">{{ __('setup.cisname_help') }}</div>
            </div>

            <div class="mb-3">
                <label for="organisation_name" class="form-label">{{ __('setup.organisation_name') }} <span class="text-danger">*</span></label>
                <input id="organisation_name" name="organisation_name" type="text" maxlength="60" required
                       class="form-control" value="{{ old('organisation_name', $identity['organisation_name']) }}">
                <div class="form-text">{{ __('setup.organisation_name_help') }}</div>
            </div>

            <div class="mb-3">
                <label for="admin_email" class="form-label">{{ __('setup.admin_email') }} <span class="text-danger">*</span></label>
                <input id="admin_email" name="admin_email" type="email" maxlength="60" required
                       class="form-control" value="{{ old('admin_email', $identity['admin_email']) }}">
                <div class="form-text">{{ __('setup.admin_email_help') }}</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">{{ __('setup.description') }}</label>
                <textarea id="description" name="description" maxlength="255" rows="2"
                          class="form-control">{{ old('description', $identity['association_dept_name']) }}</textarea>
                <div class="form-text">{{ __('setup.description_help') }}</div>
            </div>

            <div class="mb-4">
                <label for="logo" class="form-label">{{ __('setup.logo') }}</label>
                <input id="logo" name="logo" type="file" accept="image/*" class="form-control">
                <div class="form-text">{{ __('setup.logo_help') }}</div>
            </div>

            <button type="submit" class="btn btn-success w-100">
                <i class="fas fa-check me-1"></i> {{ __('setup.submit') }}
            </button>
        </form>
    </div>

    <div class="text-center text-muted mt-3" style="font-size:var(--font-size-xs);">
        {{ date('Y') }} — {{ config('app.name') }}
    </div>
</div>

@vite('resources/js/app.js')
</body>
</html>
