@extends('layout.app')

@section('title', __('statistics.financial_title') . ' — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
        ['label' => __('statistics.title'), 'url' => route('statistics.dashboard')],
        ['label' => __('statistics.financial_breadcrumb')],
    ]" />

    <x-ob-toolbar
        title="{{ __('statistics.financial_title') }}"
        :total="count($sections)"
        total-label="section"
        filter-action="{{ route('statistics.financial-report') }}"
        filter-cols="minmax(0,1.5fr) 1fr 1fr auto">

        <button type="button" class="btn btn-sm btn-light" onclick="window.print()" title="{{ __('common.print') }}">
            <i class="fas fa-print"></i>
        </button>
        <a class="btn btn-sm btn-light"
           href="{{ route('statistics.financial-report.export.xls', request()->query()) }}"
           title="{{ __('components.export_xls_title') }}">
            <i class="far fa-file-excel" style="color:var(--color-excel);"></i>
        </a>
        <a class="btn btn-sm btn-light"
           href="{{ route('statistics.financial-report.export.csv', request()->query()) }}"
           title="{{ __('components.export_csv_title') }}">
            <i class="fas fa-file-csv" style="color:var(--text-muted-soft);"></i>
        </a>

        <x-slot:filters>
            @feature('multi_site')
            <div>
                <x-ob-section-select :selected="$sectionId"
                    all-label="{{ __('statistics.financial_all_sections') }}" />
            </div>
            @endfeature

            <div class="ob-finrep-field">
                <label for="finrep-from">{{ __('statistics.financial_from') }}</label>
                <input type="date" id="finrep-from" name="from" value="{{ $from }}"
                       class="form-control form-control-sm">
            </div>

            <div class="ob-finrep-field">
                <label for="finrep-to">{{ __('statistics.financial_to') }}</label>
                <input type="date" id="finrep-to" name="to" value="{{ $to }}"
                       class="form-control form-control-sm">
            </div>

            <div class="d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-filter me-1"></i>{{ __('statistics.financial_show') }}
                </button>
            </div>
        </x-slot:filters>
    </x-ob-toolbar>

    <div class="mx-3 mt-3">
        @if (empty($sections))
            <div class="ob-widget-card">
                <div class="ob-widget-card-body">
                    <p class="ob-widget-empty mb-0">{{ __('statistics.financial_no_data') }}</p>
                </div>
            </div>
        @else
            <div class="ob-widget-card">
                <div class="ob-widget-card-body p-0">
                    <div class="table-responsive">
                        <table class="ob-table ob-finrep-table">
                            <thead>
                                <tr>
                                    <th>{{ __('statistics.financial_col_section') }}</th>
                                    <th class="text-center">{{ __('statistics.financial_col_effectif') }}</th>
                                    <th>{{ __('statistics.financial_col_profession') }}</th>
                                    @foreach ($paymentTypes as $label)
                                        <th class="text-end">{{ $label }}</th>
                                    @endforeach
                                    <th class="text-end">{{ __('statistics.financial_col_rejets') }}</th>
                                    <th class="text-end">{{ __('statistics.financial_col_total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sections as $section)
                                    @php $rowspan = count($section['lines']) + 1; @endphp

                                    @foreach ($section['lines'] as $li => $line)
                                        <tr>
                                            @if ($li === 0)
                                                <td rowspan="{{ $rowspan }}" class="ob-finrep-section">{{ $section['label'] }}</td>
                                                <td rowspan="{{ $rowspan }}" class="text-center">{{ $section['effectifs'] }}</td>
                                            @endif
                                            <td>{{ $line['profession'] }}</td>
                                            @foreach ($paymentTypes as $tpId => $label)
                                                <td class="text-end">{{ \App\Support\Money::format($line['amounts'][$tpId] ?? 0) }}</td>
                                            @endforeach
                                            <td class="text-end ob-finrep-rejets">{{ \App\Support\Money::format($line['rejets']) }}</td>
                                            <td class="text-end fw-semibold">{{ \App\Support\Money::format($line['total']) }}</td>
                                        </tr>
                                    @endforeach

                                    <tr class="ob-finrep-subtotal">
                                        @if (count($section['lines']) === 0)
                                            <td class="ob-finrep-section">{{ $section['label'] }}</td>
                                            <td class="text-center">{{ $section['effectifs'] }}</td>
                                        @endif
                                        <td>{{ __('statistics.financial_subtotal') }}</td>
                                        @foreach ($paymentTypes as $tpId => $label)
                                            <td class="text-end">{{ \App\Support\Money::format($section['subtotal']['amounts'][$tpId] ?? 0) }}</td>
                                        @endforeach
                                        <td class="text-end ob-finrep-rejets">{{ \App\Support\Money::format($section['subtotal']['rejets']) }}</td>
                                        <td class="text-end fw-semibold">{{ \App\Support\Money::format($section['subtotal']['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="ob-finrep-total">
                                    <td>{{ __('statistics.financial_grand_total') }}</td>
                                    <td class="text-center">{{ $totals['effectifs'] }}</td>
                                    <td></td>
                                    @foreach ($paymentTypes as $tpId => $label)
                                        <td class="text-end">{{ \App\Support\Money::format($totals['amounts'][$tpId] ?? 0) }}</td>
                                    @endforeach
                                    <td class="text-end">{{ \App\Support\Money::format($totals['rejets']) }}</td>
                                    <td class="text-end fw-bold">{{ \App\Support\Money::format($totals['total']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <p class="text-muted mt-2" style="font-size:var(--font-size-sm)">
                <i class="fas fa-circle-info me-1"></i>{{ __('statistics.financial_footnote') }}
            </p>
        @endif
    </div>

@endsection
