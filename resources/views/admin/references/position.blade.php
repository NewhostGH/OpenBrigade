@extends('layout.app')

@section('title', __('admin.references.position.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.references.title'), 'url' => route('admin.references')],
    ['label' => __('admin.references.team.title'), 'url' => route('admin.references.team')],
    ['label' => __('admin.references.position.title')],
]"/>

<div class="mx-3 mt-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2 mb-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible py-2 mb-3">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>{{ __('admin.references.position.new_title') }}</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.position.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3" style="font-size:var(--font-size-sm);">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-auto">
                        <label class="form-label form-label-sm">{{ __('admin.references.position.form_type') }} <span class="text-danger">*</span></label>
                        <select name="EQ_ID" class="form-select form-select-sm" required style="width:180px;">
                            <option value="">{{ __('admin.references.choose') }}</option>
                            @foreach($teams as $t)
                                <option value="{{ $t->EQ_ID }}" @selected(old('EQ_ID', $filterEq) == $t->EQ_ID)>{{ $t->EQ_NOM }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">{{ __('admin.references.position.form_shortcode') }} <span class="text-danger">*</span></label>
                        <input type="text" name="TYPE" value="{{ old('TYPE') }}"
                               class="form-control form-control-sm text-uppercase" maxlength="20" required
                               style="width:110px;" placeholder="PSE1">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">{{ __('admin.references.position.form_desc') }} <span class="text-danger">*</span></label>
                        <input type="text" name="DESCRIPTION" value="{{ old('DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="60" required
                               placeholder="Premiers Secours en Équipe Niveau 1">
                    </div>
                </div>
                <div class="row g-2 align-items-center mb-2">
                    @foreach([
                        ['PS_FORMATION', __('admin.references.position.cb_formation')],
                        ['PS_SECOURISME', __('admin.references.position.cb_secourisme')],
                        ['PS_RECYCLE', __('admin.references.position.cb_recycle')],
                        ['PS_EXPIRABLE', __('admin.references.position.cb_expirable')],
                        ['PS_DIPLOMA', __('admin.references.position.cb_diploma')],
                        ['PS_AUDIT', __('admin.references.position.cb_audit')],
                        ['PS_USER_MODIFIABLE', __('admin.references.position.cb_user_mod')],
                        ['PS_NATIONAL', __('admin.references.position.cb_national')],
                    ] as [$name, $label])
                    <div class="col-auto">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="{{ $name }}" id="new_{{ $name }}" value="1"
                                   @checked(old($name))>
                            <label class="form-check-label" for="new_{{ $name }}"
                                   style="font-size:var(--font-size-xs);">{{ $label }}</label>
                        </div>
                    </div>
                    @endforeach
                    <div class="col-auto">
                        <label class="form-label form-label-sm mb-0">{{ __('admin.references.position.warning_days') }}</label>
                        <select name="DAYS_WARNING" class="form-select form-select-sm" style="width:160px;">
                            <option value="0">{{ __('admin.references.position.day_none') }}</option>
                            <option value="7">{{ __('admin.references.position.day_7') }}</option>
                            <option value="30">{{ __('admin.references.position.day_30') }}</option>
                            <option value="60" selected>{{ __('admin.references.position.day_60') }}</option>
                            <option value="90">{{ __('admin.references.position.day_90') }}</option>
                            <option value="180">{{ __('admin.references.position.day_180') }}</option>
                            <option value="365">{{ __('admin.references.position.day_365') }}</option>
                        </select>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>{{ __('common.add') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Filter --}}
    <div class="d-flex align-items-center gap-2 mb-2">
        <span style="font-size:var(--font-size-sm);" class="text-muted">{{ __('admin.references.position.filter_label') }}</span>
        <a href="{{ route('admin.references.position') }}"
           class="btn btn-sm {{ $filterEq == 0 ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ __('admin.references.position.filter_all') }}</a>
        @foreach($teams as $t)
        <a href="{{ route('admin.references.position', ['eq' => $t->EQ_ID]) }}"
           class="btn btn-sm {{ $filterEq == $t->EQ_ID ? 'btn-secondary' : 'btn-outline-secondary' }}">
            {{ $t->EQ_NOM }}
        </a>
        @endforeach
    </div>

    {{-- List --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-list me-2"></i>{{ __('admin.references.position.list_title', ['count' => $positions->count()]) }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:140px;">{{ __('admin.references.position.col_type') }}</th>
                        <th style="width:100px;">{{ __('admin.references.position.col_code') }}</th>
                        <th>{{ __('admin.references.col_description') }}</th>
                        <th class="text-center" style="width:40px;" title="{{ __('admin.references.position.cb_formation') }}"><i class="fas fa-graduation-cap"></i></th>
                        <th class="text-center" style="width:40px;" title="{{ __('admin.references.position.cb_secourisme') }}"><i class="fas fa-first-aid"></i></th>
                        <th class="text-center" style="width:40px;" title="{{ __('admin.references.position.cb_recycle') }}"><i class="fas fa-sync"></i></th>
                        <th class="text-center" style="width:40px;" title="{{ __('admin.references.position.cb_expirable') }}"><i class="fas fa-clock"></i></th>
                        <th class="text-center" style="width:40px;" title="{{ __('admin.references.position.col_diploma') }}"><i class="fas fa-certificate"></i></th>
                        <th style="width:90px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($positions as $pos)
                    <tr>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-xs);">{{ $pos->PS_ID }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-xs);">{{ $pos->EQ_NOM }}</td>
                        <td class="align-middle font-monospace fw-semibold" style="font-size:var(--font-size-sm);">{{ $pos->TYPE }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $pos->DESCRIPTION }}</td>
                        <td class="text-center align-middle">
                            @if($pos->PS_FORMATION)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_SECOURISME)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_RECYCLE)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_EXPIRABLE)
                                <i class="fas fa-check text-success"
                                   title="{{ $pos->DAYS_WARNING > 0 ? __('admin.references.position.alert_days_before', ['days' => $pos->DAYS_WARNING]) : '' }}"></i>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($pos->PS_DIPLOMA)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="align-middle text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#editPoste{{ $pos->PS_ID }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.references.position.destroy', $pos->PS_ID) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('{{ __('admin.references.position.delete_confirm', ['type' => addslashes($pos->TYPE), 'description' => addslashes($pos->DESCRIPTION)]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Edit modal --}}
                    <div class="modal fade" id="editPoste{{ $pos->PS_ID }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.references.position.update', $pos->PS_ID) }}">
                                    @csrf @method('PATCH')
                                    <div class="modal-header">
                                        <h6 class="modal-title">{{ __('admin.references.position.modal_edit_title', ['code' => $pos->TYPE]) }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-auto">
                                                <label class="form-label form-label-sm">{{ __('admin.references.position.form_type') }} <span class="text-danger">*</span></label>
                                                <select name="EQ_ID" class="form-select form-select-sm" required>
                                                    @foreach($teams as $t)
                                                        <option value="{{ $t->EQ_ID }}" @selected($t->EQ_ID == $pos->EQ_ID)>{{ $t->EQ_NOM }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <label class="form-label form-label-sm">{{ __('admin.references.position.form_shortcode') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="TYPE" value="{{ $pos->TYPE }}"
                                                       class="form-control form-control-sm text-uppercase" maxlength="20" required style="width:110px;">
                                            </div>
                                            <div class="col">
                                                <label class="form-label form-label-sm">{{ __('admin.references.position.form_desc') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="DESCRIPTION" value="{{ $pos->DESCRIPTION }}"
                                                       class="form-control form-control-sm" maxlength="60" required>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            @foreach([
                                                ['PS_FORMATION', __('admin.references.position.cb_formation')],
                                                ['PS_SECOURISME', __('admin.references.position.cb_secourisme')],
                                                ['PS_RECYCLE', __('admin.references.position.cb_recycle')],
                                                ['PS_EXPIRABLE', __('admin.references.position.cb_expirable')],
                                                ['PS_DIPLOMA', __('admin.references.position.cb_diploma')],
                                                ['PS_AUDIT', __('admin.references.position.cb_audit')],
                                                ['PS_USER_MODIFIABLE', __('admin.references.position.cb_user_mod')],
                                                ['PS_NATIONAL', __('admin.references.position.cb_national')],
                                            ] as [$name, $label])
                                            <div class="col-auto">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="{{ $name }}" id="edit_{{ $pos->PS_ID }}_{{ $name }}"
                                                           value="1" @checked($pos->$name)>
                                                    <label class="form-check-label" for="edit_{{ $pos->PS_ID }}_{{ $name }}"
                                                           style="font-size:var(--font-size-sm);">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                            <div class="col-12 mt-2">
                                                <label class="form-label form-label-sm">{{ __('admin.references.position.warning_days_edit') }}</label>
                                                <select name="DAYS_WARNING" class="form-select form-select-sm" style="width:180px;">
                                                    @foreach([0=>__('admin.references.position.day_none'),7=>__('admin.references.position.day_7_short'),30=>__('admin.references.position.day_30_short'),60=>__('admin.references.position.day_60_short'),90=>__('admin.references.position.day_90_short'),180=>__('admin.references.position.day_180_short'),365=>__('admin.references.position.day_365_short')] as $days => $label)
                                                    <option value="{{ $days }}" @selected($pos->DAYS_WARNING == $days)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>{{ __('common.save') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">{{ __('admin.references.position.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
