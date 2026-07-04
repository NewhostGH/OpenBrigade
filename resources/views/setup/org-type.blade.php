@extends('layout.app')

@section('title', __('setup.admin_title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => __('setup.admin_breadcrumb')],
]"/>

<div class="mx-3 mt-3" style="max-width:760px;">

    @if (session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}</div>
    @endif

    {{-- Current type + change form --}}
    <div class="ob-widget-card p-4 mb-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:44px;height:44px;background:var(--sidebar-bg);color:var(--sidebar-text);">
                <i class="fas fa-sitemap"></i>
            </div>
            <div>
                <div class="fw-semibold">{{ __('setup.current_type') }}</div>
                <div class="text-muted">{{ $currentLabel }}</div>
            </div>
        </div>

        <p class="text-muted" style="font-size:var(--font-size-sm);">{{ __('setup.change_type_intro') }}</p>

        <form method="POST" action="{{ route('setup.org-type.update') }}" class="row g-2 align-items-end">
            @csrf
            @method('PATCH')
            <div class="col-sm-8">
                <label for="type_organisation" class="form-label">{{ __('setup.change_type') }}</label>
                <select id="type_organisation" name="type_organisation" class="form-select">
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}" @selected($orgType === (int) $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-1"></i> {{ __('setup.save_type') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Consequences --}}
    <div class="ob-widget-card p-4 mb-3">
        <h2 class="h6">{{ __('setup.consequences_heading') }}</h2>
        <ul class="mb-0" style="font-size:var(--font-size-sm);">
            <li class="text-success">{{ __('setup.consequence_roles_kept') }}</li>
            <li>{{ __('setup.consequence_active_set') }}</li>
            <li>{{ __('setup.consequence_reset_optional') }}</li>
        </ul>
    </div>

    {{-- Destructive: reset preset roles --}}
    <div class="ob-widget-card p-4 border border-danger-subtle">
        <h2 class="h6 text-danger"><i class="fas fa-triangle-exclamation me-1"></i>{{ __('setup.reset_roles_heading') }}</h2>
        <p class="text-muted" style="font-size:var(--font-size-sm);">{{ __('setup.reset_roles_warning') }}</p>

        <form method="POST" action="{{ route('setup.org-type.reset-roles') }}"
              onsubmit="return confirm(@js(__('setup.reset_roles_warning')));">
            @csrf
            <input type="hidden" name="type_organisation" value="{{ $orgType }}">
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-rotate-left me-1"></i> {{ __('setup.reset_roles_confirm') }} ({{ $currentLabel }})
            </button>
        </form>
    </div>

    {{-- Destructive: delete custom roles with member remap --}}
    <div class="ob-widget-card p-4 mt-3 border border-danger-subtle">
        <h2 class="h6 text-danger"><i class="fas fa-user-slash me-1"></i>{{ __('setup.delete_roles_heading') }}</h2>

        @if ($customRoles->isEmpty())
            <p class="text-muted mb-0" style="font-size:var(--font-size-sm);">{{ __('setup.delete_roles_none_custom') }}</p>
        @else
            <p class="text-muted" style="font-size:var(--font-size-sm);">{{ __('setup.delete_roles_intro') }}</p>

            <form method="POST" action="{{ route('setup.org-type.delete-custom-roles') }}"
                  onsubmit="return confirm(@js(__('setup.delete_roles_confirm_prompt')));">
                @csrf
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th style="width:2.5rem;"></th>
                                <th>{{ __('setup.delete_roles_col_role') }}</th>
                                <th class="text-center">{{ __('setup.delete_roles_col_members') }}</th>
                                <th>{{ __('setup.delete_roles_col_remap') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customRoles as $role)
                                <tr>
                                    <td>
                                        <input class="form-check-input" type="checkbox"
                                               name="roles[]" value="{{ $role->id }}"
                                               id="role_{{ $role->id }}">
                                    </td>
                                    <td><label for="role_{{ $role->id }}" class="mb-0">{{ $role->name }}</label></td>
                                    <td class="text-center">
                                        <span class="ob-badge ob-badge-int">{{ $role->members }}</span>
                                    </td>
                                    <td>
                                        <select name="remap[{{ $role->id }}]" class="form-select form-select-sm">
                                            <option value="">{{ __('setup.delete_roles_remap_drop') }}</option>
                                            @foreach ($presetRoles as $preset)
                                                <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash me-1"></i> {{ __('setup.delete_roles_confirm') }}
                </button>
            </form>
        @endif
    </div>

</div>

@endsection
