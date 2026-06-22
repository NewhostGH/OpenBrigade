@extends('layout.app')

@section('title', 'Maintenance — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.maintenance.title')],
]"/>

<div class="mx-3 mt-3">

    {{-- System info --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-server me-2"></i>{{ __('admin.maintenance.system_section') }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <tbody>
                    <tr>
                        <td style="width:35%;font-size:var(--font-size-sm);" class="fw-semibold">{{ __('admin.maintenance.row_app_version') }}</td>
                        <td><span class="ob-badge ob-badge-int">{{ $appVersion }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">{{ __('admin.maintenance.row_laravel') }}</td>
                        <td style="font-size:var(--font-size-sm);">{{ $laravelVersion }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">{{ __('admin.maintenance.row_php') }}</td>
                        <td style="font-size:var(--font-size-sm);">{{ $phpVersion }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">{{ __('admin.maintenance.row_db') }}</td>
                        <td style="font-size:var(--font-size-sm);">{{ $dbVersion }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">{{ __('admin.maintenance.row_env') }}</td>
                        <td><span class="ob-badge {{ $env === 'production' ? 'ob-badge-bloqued' : 'ob-badge-ext' }}">{{ $env }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">{{ __('admin.maintenance.row_debug') }}</td>
                        <td>
                            <span class="ob-badge {{ $debugMode === 'Activé' ? 'ob-badge-bloqued' : 'ob-badge-int' }}">
                                {{ $debugMode }}
                            </span>
                            @if($debugMode === 'Activé')
                                <span class="ms-2 text-danger" style="font-size:var(--font-size-xs);">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ __('admin.maintenance.debug_warn') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Migration status --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-database me-2"></i>{{ __('admin.maintenance.migration_section') }}</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                {!! __('admin.maintenance.migration_hint') !!}
            </div>
        </div>
        @if(empty($status))
            <div class="p-3 text-muted" style="font-size:var(--font-size-sm);">
                {{ __('admin.maintenance.migration_unavail') }}
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px;">{{ __('admin.maintenance.col_status') }}</th>
                            <th>{{ __('admin.maintenance.col_migration') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($status as $m)
                        <tr>
                            <td class="align-middle">
                                @if($m['ran'])
                                    <span class="ob-badge ob-badge-int"><i class="fas fa-check me-1"></i>{{ __('admin.maintenance.status_ran') }}</span>
                                @else
                                    <span class="ob-badge ob-badge-bloqued"><i class="fas fa-clock me-1"></i>{{ __('admin.maintenance.status_pending') }}</span>
                                @endif
                            </td>
                            <td class="align-middle font-monospace" style="font-size:var(--font-size-xs);">
                                {{ $m['name'] }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Cache / storage --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-broom me-2"></i>{{ __('admin.maintenance.commands_section') }}</div>
        </div>
        <div class="p-3">
            <table class="table table-sm mb-0" style="font-size:var(--font-size-sm);">
                <tbody>
                    <tr>
                        <td class="font-monospace" style="width:50%;">php artisan migrate</td> {{-- i18n-ignore --}}
                        <td>{{ __('admin.maintenance.cmd_migrate') }}</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan cache:clear</td> {{-- i18n-ignore --}}
                        <td>{{ __('admin.maintenance.cmd_cache_clear') }}</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan view:clear</td> {{-- i18n-ignore --}}
                        <td>{{ __('admin.maintenance.cmd_view_clear') }}</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan config:clear</td> {{-- i18n-ignore --}}
                        <td>{{ __('admin.maintenance.cmd_config_clear') }}</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan route:clear</td> {{-- i18n-ignore --}}
                        <td>{{ __('admin.maintenance.cmd_route_clear') }}</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">npm run build</td> {{-- i18n-ignore --}}
                        <td>{{ __('admin.maintenance.cmd_npm_build') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
