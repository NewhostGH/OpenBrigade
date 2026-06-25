@extends('layout.app')

@section('title', __('admin.monitoring.title') . ' — ' . config('app.name'))

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-submit settings toggles / selects.
    document.querySelectorAll('.ob-obs-toggle, .ob-obs-select').forEach(function (el) {
        el.addEventListener('change', function () { this.closest('form').submit(); });
    });
});
</script>
@endpush

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.monitoring.title')],
]"/>

<div class="mx-3 mt-3">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'logs' ? 'active' : '' }}"
               href="{{ route('admin.monitoring', ['tab' => 'logs']) }}">
                <i class="fas fa-stream me-1"></i> {{ __('admin.monitoring.tab_logs') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'health' ? 'active' : '' }}"
               href="{{ route('admin.monitoring', ['tab' => 'health']) }}">
                <i class="fas fa-heart-pulse me-1"></i> {{ __('admin.monitoring.tab_health') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'settings' ? 'active' : '' }}"
               href="{{ route('admin.monitoring', ['tab' => 'settings']) }}">
                <i class="fas fa-sliders me-1"></i> {{ __('admin.monitoring.tab_settings') }}
            </a>
        </li>
    </ul>
</div>

{{-- ── Journaux (ob_log_entry) — unified structured log + activity ─────── --}}
@if ($tab === 'logs')
    <x-ob-toolbar
        title="{{ __('admin.monitoring.tab_logs') }}"
        :total="$items->total()"
        filter-action="{{ route('admin.monitoring') }}"
        filter-id="filterForm"
        filter-cols="2fr 1fr 1fr"
        :columns="$columns"
        table-id="logsTable">
        <x-slot:filters>
            <input type="hidden" name="tab" value="logs">
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm"
                   placeholder="{{ __('common.search_placeholder') }}"
                   data-ob-search="filterForm">
            <select name="channel" class="form-select form-select-sm">
                <option value="ALL" @selected($channel === 'ALL')>{{ __('admin.monitoring.all_channels') }}</option>
                @foreach($channels as $ch)
                    <option value="{{ $ch }}" @selected($channel === $ch)>{{ $ch }}</option>
                @endforeach
            </select>
            <select name="level" class="form-select form-select-sm">
                <option value="ALL" @selected($level === 'ALL')>{{ __('admin.monitoring.all_levels') }}</option>
                @foreach($levels as $lvl)
                    <option value="{{ $lvl }}" @selected($level === $lvl)>{{ ucfirst($lvl) }}</option>
                @endforeach
            </select>
        </x-slot:filters>
    </x-ob-toolbar>

    <x-ob-commandbar table-id="logsTable" :total="$items->total()" total-label="entrée">
        <x-ob-table
            :columns="$columns"
            :items="$items"
            storage-key="obLogColsV1"
            :show-select="false"
            empty-text="{{ __('admin.monitoring.empty_logs') }}"
            table-id="logsTable"
        />
        <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
    </x-ob-commandbar>
@endif

{{-- ── Santé & performance ────────────────────────────────────────────── --}}
@if ($tab === 'health')
    @php
        $statusBadge = ['ok' => 'ob-badge-int', 'degraded' => 'ob-badge-ben', 'down' => 'ob-badge-bloqued', 'skipped' => 'ob-badge-archive'];
    @endphp
    <div class="mx-3 mt-3 row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-heart-pulse me-1"></i> {{ __('admin.monitoring.health_title') }}</span>
                    <span class="ob-badge {{ $statusBadge[$report['status']] ?? 'ob-badge-ext' }}">{{ strtoupper($report['status']) }}</span>
                </div>
                <div class="card-body">
                    <div class="text-muted mb-2" style="font-size:var(--font-size-xs);">{{ $report['version'] }} · {{ $report['timestamp'] }}</div>
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach ($report['checks'] as $name => $check)
                                <tr>
                                    <td style="text-transform:capitalize;">{{ $name }}</td>
                                    <td><span class="ob-badge {{ $statusBadge[$check['status']] ?? 'ob-badge-ext' }}">{{ $check['status'] }}</span></td>
                                    <td class="text-muted" style="font-size:var(--font-size-xs);">
                                        @foreach ($check as $k => $v)
                                            @if ($k !== 'status'){{ $k }}: {{ is_bool($v) ? ($v ? 'true' : 'false') : $v }}@if(!$loop->last) · @endif @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <a href="{{ route('health') }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="fas fa-up-right-from-square me-1"></i> {{ __('admin.monitoring.health_json') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><i class="fas fa-gauge-high me-1"></i> {{ __('admin.monitoring.perf_title') }}</div>
                <div class="card-body">
                    <div class="d-flex gap-4 mb-3">
                        <div><div class="h4 mb-0">{{ $perf['count'] }}</div><div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.monitoring.perf_slow_24h') }}</div></div>
                        <div><div class="h4 mb-0">{{ $perf['avg_ms'] ?? '—' }} <small>{{ __('admin.monitoring.settings.ms') }}</small></div><div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.monitoring.perf_avg') }}</div></div>
                        <div><div class="h4 mb-0">{{ $perf['max_ms'] ?? '—' }} <small>{{ __('admin.monitoring.settings.ms') }}</small></div><div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.monitoring.perf_max') }}</div></div>
                    </div>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>{{ __('admin.monitoring.col_date') }}</th><th>{{ __('admin.monitoring.col_route') }}</th><th class="text-end">{{ __('admin.monitoring.settings.ms') }}</th></tr></thead>
                        <tbody>
                            @forelse ($perf['slow'] as $row)
                                <tr>
                                    <td style="font-size:var(--font-size-xs);white-space:nowrap;">{{ $row->created_at?->format('d/m H:i') }}</td>
                                    <td style="font-size:var(--font-size-xs);">{{ $row->context['route'] ?? $row->url }}</td>
                                    <td class="text-end">{{ $row->duration_ms }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">{{ __('admin.monitoring.empty_perf') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Diagnostic — deliberately trigger an issue to test the pipeline --}}
    <div class="mx-3 mt-3">
        <div class="card border-warning-subtle">
            <div class="card-header"><i class="fas fa-flask me-1"></i> {{ __('admin.monitoring.diag.title') }}</div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">{{ __('admin.monitoring.diag.hint') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.monitoring.simulate') }}"
                          onsubmit="return confirm('{{ __('admin.monitoring.diag.exception_confirm') }}');">
                        @csrf
                        <input type="hidden" name="type" value="exception">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-bug me-1"></i> {{ __('admin.monitoring.diag.exception') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.monitoring.simulate') }}">
                        @csrf
                        <input type="hidden" name="type" value="log">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-lines me-1"></i> {{ __('admin.monitoring.diag.log') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.monitoring.simulate') }}">
                        @csrf
                        <input type="hidden" name="type" value="slow">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-hourglass-half me-1"></i> {{ __('admin.monitoring.diag.slow') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ── Paramètres ─────────────────────────────────────────────────────── --}}
@if ($tab === 'settings')
    @php
        $g = fn ($name) => $obsSettings[$name] ?? null;
        $levelOptions = collect($levels)->mapWithKeys(fn ($l) => [$l => ucfirst($l)])->all();
        $hasDsn = ! empty($g('obs_sentry_dsn')?->VALUE);
    @endphp
    <div class="mx-3 mt-3">

        <div class="row g-3 mb-3">
            {{-- Per-canal log levels --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="fas fa-layer-group me-1"></i> {{ __('admin.monitoring.settings.canals_title') }}</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach ($canals as $canal)
                                    @include('admin.partials.obs-select', [
                                        's' => $g($canalLevelKey($canal)),
                                        'label' => 'canal_'.$canal,
                                        'hint' => null,
                                        'default' => 'info',
                                        'options' => $levelOptions,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Outputs & retention --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="fas fa-sliders me-1"></i> {{ __('admin.monitoring.settings.outputs_title') }}</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @include('admin.partials.obs-toggle', ['s' => $g('obs_log_to_db'), 'label' => 'to_db', 'hint' => 'to_db_hint', 'default' => '1'])
                                @include('admin.partials.obs-toggle', ['s' => $g('obs_log_to_file'), 'label' => 'to_file', 'hint' => 'to_file_hint', 'default' => '1'])
                                @include('admin.partials.obs-select', ['s' => $g('obs_file_channel'), 'label' => 'file_channel', 'hint' => null, 'default' => 'daily', 'options' => ['daily' => __('admin.monitoring.settings.channel_daily'), 'single' => __('admin.monitoring.settings.channel_single')]])
                                @include('admin.partials.obs-number', ['s' => $g('obs_file_retention_days'), 'label' => 'file_retention', 'hint' => null, 'unit' => 'days', 'default' => '14', 'min' => 1, 'max' => 365])
                                @include('admin.partials.obs-number', ['s' => $g('obs_db_retention_days'), 'label' => 'db_retention', 'hint' => 'db_retention_hint', 'unit' => 'days', 'default' => '90', 'min' => 0, 'max' => 3650])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Error tracking --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-bug me-1"></i> {{ __('admin.monitoring.settings.error_title') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @include('admin.partials.obs-text', ['s' => $g('obs_sentry_dsn'), 'label' => 'sentry_dsn', 'hint' => 'sentry_dsn_hint', 'default' => '', 'type' => 'url', 'placeholder' => 'https://…@glitchtip:8000/1'])
                        <tr>
                            <td class="ps-3" style="vertical-align:middle;font-size:var(--font-size-sm);">
                                {{ __('admin.monitoring.settings.error_tracking') }}
                                <div class="text-muted" style="font-size:var(--font-size-xs);">
                                    {{ __('admin.monitoring.settings.error_tracking_hint') }}
                                    @if (! $hasDsn)
                                        <span class="text-warning"><i class="fas fa-triangle-exclamation"></i> {{ __('admin.monitoring.settings.no_dsn') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="vertical-align:middle;">
                                <form method="POST" action="{{ route('admin.settings.save', $g('obs_error_tracking')->ID) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="_back" value="monitoring">
                                    <input type="hidden" name="_tab" value="settings">
                                    <input type="hidden" name="toggle" value="1">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ob-obs-toggle" type="checkbox" name="VALUE" value="1"
                                               {{ ($g('obs_error_tracking')->VALUE ?? '0') == '1' ? 'checked' : '' }}>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Performance --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-gauge-high me-1"></i> {{ __('admin.monitoring.settings.perf_title') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @include('admin.partials.obs-toggle', ['s' => $g('obs_perf_enabled'), 'label' => 'perf_enabled', 'hint' => 'perf_enabled_hint', 'default' => '1'])
                        @include('admin.partials.obs-number', ['s' => $g('obs_perf_slow_ms'), 'label' => 'perf_slow', 'hint' => null, 'unit' => 'ms', 'default' => '1000', 'min' => 50, 'max' => 60000])
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
