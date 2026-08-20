@extends('layout.app')

@section('title', __('planning.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('planning.breadcrumb')],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>{{ __('planning.title') }}</h1>
        <a href="{{ route('planning.print') }}" data-ob-calendar-print target="_blank"
           class="btn btn-sm btn-outline-secondary ms-auto" title="{{ __('planning.export_pdf_title') }}">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
    </div>
</div>

<div class="mx-3 mt-3">
    <div class="ob-widget-card">
        <div class="ob-widget-card-body">
            <div data-ob-calendar
                 data-events-url="{{ route('planning.events') }}"
                 data-print-url="{{ route('planning.print') }}"
                 data-initial-view="dayGridMonth"></div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="ob-widget-card mt-2">
        <div class="ob-widget-card-body py-2">
            <div class="ob-cal-legend">
                <span><span class="ob-cal-legend-dot ob-cal-legend-dot--activity"></span> {{ __('planning.legend_event') }}</span>
                <span><span class="ob-cal-legend-dot ob-cal-legend-dot--abs-ok"></span> {{ __('planning.legend_abs_ok') }}</span>
                <span><span class="ob-cal-legend-dot ob-cal-legend-dot--abs-pending"></span> {{ __('planning.legend_abs_pending') }}</span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/ob-calendar.js')
@endpush
