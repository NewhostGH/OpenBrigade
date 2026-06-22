{{--
Shared tab navigation + year selector + print button for bilan pages.
Required variables: $year, $years, $activeTab ('generalites' | 'activites' | 'formations')
--}}
<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>{{ __('statistics.annual_report_title') }} {{ $year }}</h1>
        <form method="GET" action="{{ url()->current() }}" class="ob-stats-year-form">
            <label class="text-muted" style="font-size:var(--font-size-sm)">{{ __('statistics.year_label') }}</label>
            <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="ob-toolbar-actions">
        <a href="{{ route('statistics.dashboard') }}?year={{ $year }}"
            class="btn btn-sm btn-outline-secondary btn-print-hide">
            <i class="fas fa-chart-line me-1"></i>{{ __('statistics.btn_dashboard') }}
        </a>
        <button type="button" id="btn-download-pdf" class="btn btn-sm btn-primary btn-print-hide">
            <i class="fas fa-file-pdf me-1"></i>{{ __('statistics.btn_download_pdf') }}
        </button>
    </div>
</div>

<div class="mx-3 mt-2 ob-ob-bilan-tabs btn-print-hide">
    <a href="{{ route('statistics.annual-report.overview', ['year' => $year]) }}"
        class="ob-bilan-tab {{ $activeTab === 'generalites' ? 'ob-bilan-tab--active' : '' }}">
        <i class="fas fa-users me-1"></i>{{ __('statistics.tab_generalites') }}
    </a>
    <a href="{{ route('statistics.annual-report.activities', ['year' => $year]) }}"
        class="ob-bilan-tab {{ $activeTab === 'activites' ? 'ob-bilan-tab--active' : '' }}">
        <i class="fas fa-fire me-1"></i>{{ __('statistics.tab_activites') }}
    </a>
    <a href="{{ route('statistics.annual-report.training', ['year' => $year]) }}"
        class="ob-bilan-tab {{ $activeTab === 'formations' ? 'ob-bilan-tab--active' : '' }}">
        <i class="fas fa-graduation-cap me-1"></i>{{ __('statistics.tab_formations') }}
    </a>
</div>