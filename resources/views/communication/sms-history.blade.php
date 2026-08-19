@extends('layout.app')

@section('title', __('communication.sms_title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('communication.sms_breadcrumb')],
]" />

<x-ob-toolbar
    title="{{ __('communication.sms_title') }}"
    :total="$items->total()"
    filter-action="{{ route('communication.sms-history') }}"
    filter-id="smsHistoryForm"
    filter-cols="1fr 1fr auto"
    table-id="smsHistoryTable"
    :columns="$columns">

    <x-slot:filters>
        <input type="hidden" name="perPage" value="{{ request('perPage', 50) }}">

        @feature('multi_site')
        <x-ob-section-select :selected="$sectionId" name="section" id="sms-section"
            all-label="{{ __('communication.sms_all_sections') }}" />
        @endfeature

        <div class="d-flex align-items-center gap-2">
            <label for="sms-from" class="form-label form-label-sm mb-0">{{ __('communication.sms_from') }}</label>
            <input type="date" id="sms-from" name="from" value="{{ $from }}"
                   class="form-control form-control-sm">
            <label for="sms-to" class="form-label form-label-sm mb-0">{{ __('communication.sms_to') }}</label>
            <input type="date" id="sms-to" name="to" value="{{ $to }}"
                   class="form-control form-control-sm">
        </div>

        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="fas fa-search"></i>
        </button>
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="smsHistoryTable" :total="$items->total()"
    total-label="{{ __('communication.sms_total_label') }}" :show-sel-count="false">
    <x-ob-table :columns="$columns" :items="$items" storage-key="smsHistoryCols"
        :show-select="false" table-id="smsHistoryTable"
        empty-text="{{ __('communication.sms_empty') }}" />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
