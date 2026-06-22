@extends('layout.app')

@section('title', ($policy ? __('common.edit') : __('common.create')) . " une politique — " . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.security.title'), 'url' => route('admin.security', ['tab' => 'passwords'])],
    ['label' => $policy ? __('admin.policy.edit_title_short') : __('admin.policy.new_policy_btn')],
]"/>

<div class="mx-3 mt-3">

<form method="POST"
      action="{{ $policy ? route('admin.policy.update', $policy->id) : route('admin.policy.store') }}">
@csrf
@if ($policy)
    @method('PATCH')
@endif

<div class="row g-3">

    {{-- Main fields --}}
    <div class="col-lg-8">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-shield-alt me-1"></i>
                    {{ $policy ? __('admin.policy.edit_title', ['name' => $policy->name]) : __('admin.policy.create_title') }}
                </div>
            </div>
            <div class="ob-widget-card-body">

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">{{ __('admin.policy.field_name') }}</label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $policy?->name) }}" required maxlength="80">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Length --}}
                <h6 class="text-secondary mb-3"><i class="fas fa-ruler me-1"></i> {{ __('admin.policy.section_length') }}</h6>

                <div class="mb-4">
                    <label for="min_length" class="form-label fw-semibold">{{ __('admin.policy.min_length') }}</label>
                    <div class="input-group" style="max-width:180px;">
                        <input type="number" id="min_length" name="min_length"
                               class="form-control @error('min_length') is-invalid @enderror"
                               value="{{ old('min_length', $policy?->min_length ?? 12) }}"
                               min="6" max="128" required>
                        <span class="input-group-text">{{ __('admin.policy.min_length_unit') }}</span>
                    </div>
                    <div class="form-text">
                        {{ __('admin.policy.min_length_hint') }}
                    </div>
                    @error('min_length') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Complexity --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-sliders-h me-1"></i> {{ __('admin.policy.section_complexity') }}</h6>
                <p class="text-muted small mb-3">
                    {{ __('admin.policy.complexity_hint') }}
                </p>

                @foreach ([
                    ['require_uppercase', __('admin.policy.require_uppercase')],
                    ['require_lowercase', __('admin.policy.require_lowercase')],
                    ['require_digits',    __('admin.policy.require_digits')],
                    ['require_special',   __('admin.policy.require_special')],
                ] as [$field, $label])
                <div class="form-check mb-2">
                    <input type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1"
                           class="form-check-input"
                           {{ old($field, $policy?->{$field} ?? false) ? 'checked' : '' }}>
                    <label for="{{ $field }}" class="form-check-label">{{ $label }}</label>
                </div>
                @endforeach

                <hr>

                {{-- Expiry --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-calendar-times me-1"></i> {{ __('admin.policy.section_expiry') }}</h6>
                <p class="text-muted small mb-3">
                    {{ __('admin.policy.expiry_hint') }}
                </p>

                <div class="mb-4">
                    <label for="expiry_days" class="form-label fw-semibold">{{ __('admin.policy.expiry_days') }}</label>
                    <div class="input-group" style="max-width:200px;">
                        <input type="number" id="expiry_days" name="expiry_days"
                               class="form-control @error('expiry_days') is-invalid @enderror"
                               value="{{ old('expiry_days', $policy?->expiry_days ?? 0) }}"
                               min="0" max="3650">
                        <span class="input-group-text">{{ __('admin.policy.expiry_unit') }}</span>
                    </div>
                    <div class="form-text">{{ __('admin.policy.expiry_hint2') }}</div>
                    @error('expiry_days') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Lockout --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-ban me-1"></i> {{ __('admin.policy.section_lockout') }}</h6>

                <div class="mb-4">
                    <label for="max_attempts" class="form-label fw-semibold">{{ __('admin.policy.max_attempts') }}</label>
                    <div class="input-group" style="max-width:200px;">
                        <input type="number" id="max_attempts" name="max_attempts"
                               class="form-control @error('max_attempts') is-invalid @enderror"
                               value="{{ old('max_attempts', $policy?->max_attempts ?? 10) }}"
                               min="0" max="100">
                        <span class="input-group-text">{{ __('admin.policy.max_attempts_unit') }}</span>
                    </div>
                    <div class="form-text">{{ __('admin.policy.max_attempts_hint') }}</div>
                    @error('max_attempts') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Blocklist --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-list-ul me-1"></i> {{ __('admin.policy.section_blocklist') }}</h6>

                <div class="form-check mb-4">
                    <input type="checkbox" id="blocklist_check" name="blocklist_check" value="1"
                           class="form-check-input"
                           {{ old('blocklist_check', $policy?->blocklist_check ?? true) ? 'checked' : '' }}>
                    <label for="blocklist_check" class="form-check-label fw-semibold">
                        {{ __('admin.policy.blocklist_check') }}
                    </label>
                    <div class="form-text ms-0">
                        {{ __('admin.policy.blocklist_hint') }}
                    </div>
                </div>

                <hr>

                {{-- Require 2FA --}}
                <h6 class="text-secondary mb-2"><i class="fas fa-mobile-alt me-1"></i> {{ __('admin.policy.section_2fa') }}</h6>

                <div class="form-check mb-4">
                    <input type="checkbox" id="require_2fa" name="require_2fa" value="1"
                           class="form-check-input"
                           {{ old('require_2fa', $policy?->require_2fa ?? false) ? 'checked' : '' }}>
                    <label for="require_2fa" class="form-check-label fw-semibold">
                        {{ __('admin.policy.require_2fa') }}
                    </label>
                    <div class="form-text ms-0">
                        {{ __('admin.policy.require_2fa_hint') }}
                    </div>
                </div>

                <hr>

                {{-- Default flag --}}
                <div class="form-check mb-4">
                    <input type="checkbox" id="is_default" name="is_default" value="1"
                           class="form-check-input"
                           {{ old('is_default', $policy?->is_default ?? false) ? 'checked' : '' }}>
                    <label for="is_default" class="form-check-label fw-semibold">
                        {{ __('admin.policy.is_default') }}
                    </label>
                    <div class="form-text ms-0">
                        {{ __('admin.policy.is_default_hint') }}
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ __('common.save') }}
                    </button>
                    <a href="{{ route('admin.security', ['tab' => 'passwords']) }}" class="btn btn-outline-secondary">
                        {{ __('common.cancel') }}
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- Group assignment sidebar --}}
    <div class="col-lg-4">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-users me-1"></i> {{ __('admin.policy.groups_title') }}
                </div>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">

                <p class="text-muted mb-3">
                    {{ __('admin.policy.groups_hint') }}
                </p>

                @php($assignedIds = $policy ? $policy->groups->pluck('id')->toArray() : [])

                @forelse ($groups as $group)
                <div class="form-check mb-1">
                    <input type="checkbox"
                           id="grp_{{ $group->id }}"
                           name="group_ids[]"
                           value="{{ $group->id }}"
                           class="form-check-input"
                           {{ in_array($group->id, old('group_ids', $assignedIds)) ? 'checked' : '' }}>
                    <label for="grp_{{ $group->id }}" class="form-check-label">
                        {{ $group->name }}
                        @if ($group->is_system)
                            <span class="badge bg-secondary ms-1" style="font-size:.65em;">{{ __('admin.policy.badge_system') }}</span>
                        @endif
                    </label>
                </div>
                @empty
                <p class="text-muted mb-0">{{ __('admin.policy.no_groups') }}</p>
                @endforelse

            </div>
        </div>

    </div>

</div>
</form>
</div>

@endsection
