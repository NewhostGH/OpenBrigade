@extends('layout.app')

@section('title', __('admin.references.participation_type.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.references.title'), 'url' => route('admin.references')],
    ['label' => __('admin.references.participation_type.title')],
]"/>

<div class="mx-3 mt-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2 mb-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>{{ __('admin.references.participation_type.new_title') }}</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.participation-type.store') }}">
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
                        <label class="form-label form-label-sm">{{ __('admin.references.participation_type.event_type_label') }} <span class="text-danger">*</span></label>
                        <select name="TE_CODE" class="form-select form-select-sm" required style="min-width:180px;">
                            <option value="">{{ __('admin.references.choose') }}</option>
                            @foreach($eventTypes as $et)
                                <option value="{{ $et->TE_CODE }}" @selected(old('TE_CODE') == $et->TE_CODE)>
                                    {{ $et->TE_LIBELLE }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">{{ __('admin.references.participation_type.num_label') }} <span class="text-danger">*</span></label>
                        <input type="number" name="TP_NUM" value="{{ old('TP_NUM', 1) }}"
                               class="form-control form-control-sm" style="width:70px;" min="1" max="99" required>
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">{{ __('admin.references.participation_type.label_label') }} <span class="text-danger">*</span></label>
                        <input type="text" name="TP_LIBELLE" value="{{ old('TP_LIBELLE') }}"
                               class="form-control form-control-sm" maxlength="40" required>
                    </div>
                    <div class="col-auto">
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="INSTRUCTOR" id="new_instructor" value="1" @checked(old('INSTRUCTOR'))>
                            <label class="form-check-label" for="new_instructor" style="font-size:var(--font-size-xs);">{{ __('admin.references.participation_type.instructor_label') }}</label>
                        </div>
                    </div>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col">
                        <label class="form-label form-label-sm">{{ __('admin.references.participation_type.skill_label') }}</label>
                        <select name="PS_ID" class="form-select form-select-sm">
                            <option value="">{{ __('admin.references.none_option') }}</option>
                            @php($prevTeam = '')
                            @foreach($postes as $p)
                                @if($prevTeam !== $p->EQ_NOM)
                                    <optgroup label="{{ $p->EQ_NOM }}">
                                    @php($prevTeam = $p->EQ_NOM)
                                @endif
                                <option value="{{ $p->PS_ID }}" @selected(old('PS_ID') == $p->PS_ID)>
                                    {{ $p->TYPE }} — {{ $p->DESCRIPTION }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">{{ __('admin.references.participation_type.skill_alt_label') }}</label>
                        <select name="PS_ID2" class="form-select form-select-sm">
                            <option value="">{{ __('admin.references.none_option') }}</option>
                            @php($prevTeam = '')
                            @foreach($postes as $p)
                                @if($prevTeam !== $p->EQ_NOM)
                                    <optgroup label="{{ $p->EQ_NOM }}">
                                    @php($prevTeam = $p->EQ_NOM)
                                @endif
                                <option value="{{ $p->PS_ID }}" @selected(old('PS_ID2') == $p->PS_ID)>
                                    {{ $p->TYPE }} — {{ $p->DESCRIPTION }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>{{ __('common.add') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-users me-2"></i>{{ __('admin.references.participation_type.list_title', ['count' => $items->count()]) }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">{{ __('admin.references.participation_type.col_num') }}</th>
                        <th>{{ __('admin.references.participation_type.col_label') }}</th>
                        <th style="width:180px;">{{ __('admin.references.participation_type.col_event_type') }}</th>
                        <th style="width:160px;">{{ __('admin.references.participation_type.col_skill') }}</th>
                        <th style="width:160px;">{{ __('admin.references.participation_type.col_skill_alt') }}</th>
                        <th class="text-center" style="width:80px;">{{ __('admin.references.participation_type.col_instructor') }}</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="align-middle text-center font-monospace" style="font-size:var(--font-size-sm);">{{ $item->TP_NUM }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $item->TP_LIBELLE }}</td>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-xs);">{{ $item->te_label }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-xs);">
                            @if($item->PS_ID)
                                <span class="font-monospace fw-semibold">{{ $item->ps1_type }}</span>
                                <span class="text-muted">{{ $item->ps1_desc }}</span>
                            @endif
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-xs);">
                            @if($item->PS_ID2)
                                <span class="font-monospace fw-semibold">{{ $item->ps2_type }}</span>
                                <span class="text-muted">{{ $item->ps2_desc }}</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($item->INSTRUCTOR)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="align-middle text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#editTp{{ $item->TP_ID }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.references.participation-type.destroy', $item->TP_ID) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('{{ __('admin.references.participation_type.delete_confirm', ['label' => addslashes($item->TP_LIBELLE)]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Edit modal --}}
                    <div class="modal fade" id="editTp{{ $item->TP_ID }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.references.participation-type.update', $item->TP_ID) }}">
                                    @csrf @method('PATCH')
                                    <div class="modal-header">
                                        <h6 class="modal-title">{{ __('admin.references.participation_type.modal_edit_title', ['label' => $item->TP_LIBELLE]) }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-auto">
                                                <label class="form-label form-label-sm">{{ __('admin.references.participation_type.num_label') }} <span class="text-danger">*</span></label>
                                                <input type="number" name="TP_NUM" value="{{ $item->TP_NUM }}"
                                                       class="form-control form-control-sm" style="width:70px;" min="1" max="99" required>
                                            </div>
                                            <div class="col">
                                                <label class="form-label form-label-sm">{{ __('admin.references.participation_type.label_label') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="TP_LIBELLE" value="{{ $item->TP_LIBELLE }}"
                                                       class="form-control form-control-sm" maxlength="40" required>
                                            </div>
                                            <div class="col-auto d-flex align-items-end pb-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="INSTRUCTOR" id="edit_ins_{{ $item->TP_ID }}"
                                                           value="1" @checked($item->INSTRUCTOR)>
                                                    <label class="form-check-label" for="edit_ins_{{ $item->TP_ID }}"
                                                           style="font-size:var(--font-size-sm);">{{ __('admin.references.participation_type.instructor_label') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label form-label-sm">{{ __('admin.references.participation_type.skill_label') }}</label>
                                                <select name="PS_ID" class="form-select form-select-sm">
                                                    <option value="">{{ __('admin.references.none_option') }}</option>
                                                    @php($prevTeam = '')
                                                    @foreach($postes as $p)
                                                        @if($prevTeam !== $p->EQ_NOM)
                                                            <optgroup label="{{ $p->EQ_NOM }}">
                                                            @php($prevTeam = $p->EQ_NOM)
                                                        @endif
                                                        <option value="{{ $p->PS_ID }}" @selected($item->PS_ID == $p->PS_ID)>
                                                            {{ $p->TYPE }} — {{ $p->DESCRIPTION }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label form-label-sm">{{ __('admin.references.participation_type.skill_alt_label') }}</label>
                                                <select name="PS_ID2" class="form-select form-select-sm">
                                                    <option value="">{{ __('admin.references.none_option') }}</option>
                                                    @php($prevTeam = '')
                                                    @foreach($postes as $p)
                                                        @if($prevTeam !== $p->EQ_NOM)
                                                            <optgroup label="{{ $p->EQ_NOM }}">
                                                            @php($prevTeam = $p->EQ_NOM)
                                                        @endif
                                                        <option value="{{ $p->PS_ID }}" @selected($item->PS_ID2 == $p->PS_ID)>
                                                            {{ $p->TYPE }} — {{ $p->DESCRIPTION }}
                                                        </option>
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
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('admin.references.participation_type.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
