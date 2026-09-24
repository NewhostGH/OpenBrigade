@extends('layout.app')

@section('title', __('billable_element.title') . ' | ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('billable_element.breadcrumb_parent'), 'url' => route('company.index')],
    ['label' => __('billable_element.title')],
]"/>

<div class="mx-3 mt-3">

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2" aria-hidden="true"></i>{{ __('billable_element.new_title') }}</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('billable-element.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3 ob-billable-errors">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label form-label-sm">{{ __('billable_element.col_section') }}</label>
                        <x-ob-section-select name="S_ID" :selected="old('S_ID', $defaultSectionId)" required/>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm" for="be-type">{{ __('billable_element.col_type') }} <span class="text-danger">*</span></label>
                        <select id="be-type" name="TEF_CODE" class="form-select form-select-sm" required>
                            @foreach($types as $t)
                                <option value="{{ $t->TEF_CODE }}" @selected(old('TEF_CODE') === $t->TEF_CODE)>{{ $t->TEF_NAME }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm" for="be-name">{{ __('billable_element.col_name') }} <span class="text-danger">*</span></label>
                        <input id="be-name" type="text" name="EF_NAME" value="{{ old('EF_NAME') }}"
                               class="form-control form-control-sm" maxlength="60" required
                               placeholder="{{ __('billable_element.ph_name') }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm" for="be-price">{{ __('billable_element.col_price') }} <span class="text-danger">*</span></label>
                        <input id="be-price" type="text" inputmode="decimal" name="EF_PRICE" value="{{ old('EF_PRICE', '0') }}"
                               class="form-control form-control-sm ob-billable-price" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1" aria-hidden="true"></i>{{ __('common.add') }}
                        </button>
                    </div>
                </div>
                <div class="mt-2 ob-billable-hint">{{ __('billable_element.price_hint', ['currency' => $currency]) }}</div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header d-flex flex-wrap align-items-center gap-2">
            <div class="ob-widget-card-title me-auto"><i class="fas fa-file-invoice-dollar me-2" aria-hidden="true"></i>{{ __('billable_element.list_title', ['count' => $items->count()]) }}</div>
            <form method="GET" action="{{ route('billable-element.index') }}" class="d-flex gap-2">
                <x-ob-section-select :selected="$sectionId" :all-label="__('billable_element.all_sections')" auto-submit/>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('billable_element.col_type') }}">
                    <option value="ALL" @selected($type === 'ALL')>{{ __('billable_element.all_types') }}</option>
                    @foreach($types as $t)
                        <option value="{{ $t->TEF_CODE }}" @selected($type === $t->TEF_CODE)>{{ $t->TEF_NAME }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        @if($sectionId === null)
                            <th class="ob-billable-col-section">{{ __('billable_element.col_section') }}</th>
                        @endif
                        <th class="ob-billable-col-type">{{ __('billable_element.col_type') }}</th>
                        <th>{{ __('billable_element.col_name') }}</th>
                        <th class="ob-billable-col-price">{{ __('billable_element.col_price') }}</th>
                        <th class="ob-billable-col-actions"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        @if($sectionId === null)
                            <td class="align-middle">{{ $item->section?->S_CODE ?? __('common.empty_value') }}</td>
                        @endif
                        <td class="align-middle">
                            <select name="TEF_CODE" form="be-edit-{{ $item->EF_ID }}" class="form-select form-select-sm" aria-label="{{ __('billable_element.col_type') }}">
                                @foreach($types as $t)
                                    <option value="{{ $t->TEF_CODE }}" @selected($item->TEF_CODE === $t->TEF_CODE)>{{ $t->TEF_NAME }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="align-middle">
                            <input type="text" name="EF_NAME" form="be-edit-{{ $item->EF_ID }}" value="{{ $item->EF_NAME }}"
                                   aria-label="{{ __('billable_element.col_name') }}" class="form-control form-control-sm" maxlength="60" required>
                        </td>
                        <td class="align-middle">
                            <div class="input-group input-group-sm">
                                <input type="text" inputmode="decimal" name="EF_PRICE" form="be-edit-{{ $item->EF_ID }}"
                                       aria-label="{{ __('billable_element.col_price') }}"
                                       value="{{ number_format($item->EF_PRICE, 2, ',', '') }}" class="form-control text-end" required>
                                <span class="input-group-text">{{ $currency }}</span>
                            </div>
                        </td>
                        <td class="align-middle text-end text-nowrap">
                            <form method="POST" action="{{ route('billable-element.update', $item) }}" id="be-edit-{{ $item->EF_ID }}" class="d-inline">
                                @csrf @method('PATCH')
                            </form>
                            <button type="submit" form="be-edit-{{ $item->EF_ID }}" class="btn btn-sm btn-outline-primary"
                                    title="{{ __('billable_element.action_save') }}" aria-label="{{ __('billable_element.action_save') }}">
                                <i class="fas fa-save" aria-hidden="true"></i>
                            </button>
                            <form method="POST" action="{{ route('billable-element.store') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="S_ID" value="{{ $item->S_ID }}">
                                <input type="hidden" name="TEF_CODE" value="{{ $item->TEF_CODE }}">
                                <input type="hidden" name="EF_NAME" value="{{ \Illuminate\Support\Str::limit($item->EF_NAME, 60 - mb_strlen(__('billable_element.copy_suffix')), '') . __('billable_element.copy_suffix') }}">
                                <input type="hidden" name="EF_PRICE" value="{{ $item->EF_PRICE }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                        title="{{ __('billable_element.action_duplicate') }}" aria-label="{{ __('billable_element.action_duplicate') }}">
                                    <i class="fas fa-copy" aria-hidden="true"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('billable-element.destroy', $item) }}" class="d-inline"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('billable_element.delete_confirm', ['name' => $item->EF_NAME])) }})">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        title="{{ __('billable_element.action_delete') }}" aria-label="{{ __('billable_element.action_delete') }}">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $sectionId === null ? 5 : 4 }}" class="text-center text-muted py-4">{{ __('billable_element.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
