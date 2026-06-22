@extends('layout.app')

@section('title', 'Mes droits — ' . config('app.name'))

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.ob-ctx-auto').forEach(function (el) {
                el.addEventListener('change', function () { this.closest('form').submit(); });
            });
            document.querySelectorAll('.ob-hab-cat-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    row.closest('tbody').classList.toggle('ob-hab-collapsed');
                });
            });
        });
    </script>
@endpush

@section('content')

@php
    $obsolete   = $obsolete   ?? [];
    $userAllows = $userAllows  ?? [];
    $userDenies = $userDenies  ?? [];
@endphp

<x-ob-breadcrumb :items="[
        ['label' => __('my_permissions.breadcrumb_account')],
        ['label' => __('my_permissions.breadcrumb')],
    ]" />

<div class="mx-3 mt-3">

    {{-- Page header --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-id-card me-2"></i>{{ __('my_permissions.header_title') }}</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                {{ __('my_permissions.header_subtitle') }}
            </div>
        </div>
        <div class="p-3">
            {{-- Section + role selectors (side by side), groups always-on --}}
            <div class="d-flex align-items-center flex-wrap gap-2">
                @feature('multi_site')
                <span class="text-muted" style="font-size:var(--font-size-sm);">{{ __('my_permissions.label_section') }}</span>
                <form method="GET" action="{{ route('my-permissions') }}" style="margin:0;">
                    <input type="hidden" name="role" value="{{ $roleId }}">
                    <select name="section" class="form-select form-select-sm ob-ctx-auto" style="width:auto;">
                        @forelse ($sections as $s)
                            <option value="{{ $s->S_ID }}" {{ (int) $s->S_ID === (int) $sectionId ? 'selected' : '' }}>
                                {!! str_repeat('&nbsp;&nbsp;&nbsp;', (int) ($s->depth ?? 0)) !!}{{ ($s->depth ?? 0) > 0 ? '└ ' : '' }}{{ $s->S_DESCRIPTION }}
                            </option>
                        @empty
                            <option>—</option>
                        @endforelse
                    </select>
                </form>
                @endfeature

                <span class="text-muted ms-2" style="font-size:var(--font-size-sm);">{{ __('my_permissions.label_role') }}</span>
                <form method="GET" action="{{ route('my-permissions') }}" style="margin:0;">
                    <input type="hidden" name="section" value="{{ $sectionId }}">
                    <select name="role" class="form-select form-select-sm ob-ctx-auto" style="width:auto;">
                        <option value="">{{ __('my_permissions.all_roles') }}</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}" {{ (int) $r->id === (int) $roleId ? 'selected' : '' }}>
                                {{ $r->name }}@if (!empty($r->inherited)) {{ __('my_permissions.role_inherited') }}@endif
                            </option>
                        @endforeach
                    </select>
                </form>

                <span class="ms-auto text-muted" style="font-size:var(--font-size-xs);">
                    <i class="fas fa-lock fa-xs me-1"></i>{{ __('my_permissions.groups_always_applied') }}
                </span>
            </div>
        </div>
    </div>

    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-list-check me-2"></i>{{ __('my_permissions.effective_title') }}</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                <i class="fas fa-check text-success"></i> {{ __('my_permissions.legend_granted') }} &nbsp;
                <i class="fas fa-minus text-muted"></i> {{ __('my_permissions.legend_not_granted') }} &nbsp;
                <i class="fas fa-lock text-muted"></i> {{ __('my_permissions.legend_capped') }} &nbsp;
                <i class="fas fa-user-check" style="color:var(--color-primary);"></i> {{ __('my_permissions.legend_user_allow') }} &nbsp;
                <i class="fas fa-user-times text-danger"></i> {{ __('my_permissions.legend_user_deny') }}
            </div>
        </div>
        <div class="p-3">
            <div class="ob-hab-matrix-scroll">
                <table class="ob-hab-table">
                    <thead>
                        <tr>
                            <th class="ob-hab-feat-head">{{ __('my_permissions.col_feature') }}</th>
                            <th class="ob-hab-colhead"
                                style="min-width:80px;writing-mode:horizontal-tb;transform:none;">{{ __('my_permissions.col_granted') }}</th>
                            <th class="ob-hab-colhead"
                                style="min-width:160px;writing-mode:horizontal-tb;transform:none;text-align:left;padding:6px 8px;">
                                {{ __('my_permissions.col_origin') }}</th>
                        </tr>
                    </thead>
                    @foreach ($featuresByCategory as $category => $features)
                        <tbody data-hab-cat>
                            <tr class="ob-hab-cat-row">
                                <td colspan="3">
                                    <i class="fas fa-chevron-down ob-hab-chevron me-1"></i>{{ $category ?: __('my_permissions.category_general') }}
                                    <span class="text-muted ms-1"
                                        style="font-weight:400;text-transform:none;">({{ $features->count() }})</span>
                                </td>
                            </tr>
                            @foreach ($features as $f)
                                @php
                                    $fid        = (int) $f->F_ID;
                                    $isDenied   = in_array($fid, $denied, true);
                                    $hasUserAllow = in_array($fid, $userAllows, true);
                                    $hasUserDeny  = in_array($fid, $userDenies, true);
                                    $sources    = $origins[$fid] ?? [];
                                    $granted    = !$isDenied && !$hasUserDeny && ($hasUserAllow || !empty($sources));
                                    $isObsolete = in_array($fid, $obsolete, true);
                                @endphp
                                <tr class="ob-hab-feat {{ $isDenied ? 'ob-hab-row-capped' : ($hasUserDeny ? 'ob-hab-row-denied' : '') }}">
                                    <td class="ob-hab-feat-cell" title="{{ $f->F_DESCRIPTION }}">
                                        {{ $f->F_LIBELLE }}
                                        <span class="text-muted ms-1" style="font-size:10px;">#{{ $fid }}</span>
                                        @if ($f->F_FLAG)<span class="ob-badge ob-badge-bloqued ms-1"
                                        style="font-size:9px;">{{ __('my_permissions.badge_sensitive') }}</span>@endif
                                        @if ($isObsolete)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;"
                                        title="{{ __('my_permissions.badge_obsolete_title') }}">{{ __('my_permissions.badge_obsolete') }}</span>@endif
                                    </td>
                                    <td class="ob-hab-cell">
                                        @if ($isDenied)
                                            <i class="fas fa-lock text-muted" title="{{ __('my_permissions.icon_capped') }}"></i>
                                        @elseif ($hasUserDeny)
                                            <i class="fas fa-user-times text-danger" title="{{ __('my_permissions.icon_user_deny') }}"></i>
                                        @elseif ($hasUserAllow)
                                            <i class="fas fa-user-check" style="color:var(--color-primary);" title="{{ __('my_permissions.icon_user_allow') }}"></i>
                                        @elseif ($granted)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-minus text-muted"></i>
                                        @endif
                                    </td>
                                    <td class="ob-hab-feat-cell"
                                        style="position:static;min-width:0;font-size:11px;color:var(--text-muted-soft);">
                                        @if ($hasUserDeny)
                                            <span style="color:var(--color-danger);font-style:italic;">{{ __('my_permissions.origin_user_deny') }}</span>
                                        @elseif ($hasUserAllow)
                                            <span style="color:var(--color-primary);font-style:italic;">{{ __('my_permissions.origin_user_allow') }}</span>{{ !empty($sources) ? ' · ' . implode(' · ', $sources) : '' }}
                                        @else
                                            {{ $granted ? implode(' · ', $sources) : '—' }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

@endsection