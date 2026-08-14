@extends('layout.app')

@section('title', __('admin.references.competence_hierarchy.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')],
    ['label' => __('admin.references.title'), 'url' => route('admin.references')],
    ['label' => __('admin.references.competence_hierarchy.title')],
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

    <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
        {{ __('admin.references.competence_hierarchy.intro') }}
    </p>

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>{{ __('admin.references.competence_hierarchy.new_title') }}</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.competence-hierarchy.store') }}">
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
                        <label class="form-label form-label-sm">{{ __('admin.references.competence_hierarchy.form_code') }} <span class="text-danger">*</span></label>
                        <input type="text" name="PH_CODE" value="{{ old('PH_CODE') }}"
                               class="form-control form-control-sm text-uppercase" maxlength="15" required
                               style="width:130px;" placeholder="{{ __('admin.references.competence_hierarchy.ph_code') }}"
                               pattern="[A-Za-z0-9_]+" title="{{ __('admin.references.code_pattern_title') }}">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">{{ __('admin.references.competence_hierarchy.form_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="PH_NAME" value="{{ old('PH_NAME') }}"
                               class="form-control form-control-sm" maxlength="30" required
                               placeholder="{{ __('admin.references.competence_hierarchy.ph_name') }}">
                    </div>
                </div>
                <div class="row g-3 align-items-center mb-2">
                    <div class="col-auto">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="PH_HIDE_LOWER" id="new_PH_HIDE_LOWER" value="1" @checked(old('PH_HIDE_LOWER', true))>
                            <label class="form-check-label" for="new_PH_HIDE_LOWER" style="font-size:var(--font-size-xs);"
                                   title="{{ __('admin.references.competence_hierarchy.cb_hide_help') }}">{{ __('admin.references.competence_hierarchy.cb_hide') }}</label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="PH_UPDATE_LOWER_EXPIRY" id="new_PH_UPDATE_LOWER_EXPIRY" value="1" @checked(old('PH_UPDATE_LOWER_EXPIRY', true))>
                            <label class="form-check-label" for="new_PH_UPDATE_LOWER_EXPIRY" style="font-size:var(--font-size-xs);"
                                   title="{{ __('admin.references.competence_hierarchy.cb_expiry_help') }}">{{ __('admin.references.competence_hierarchy.cb_expiry') }}</label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="PH_UPDATE_MANDATORY" id="new_PH_UPDATE_MANDATORY" value="1" @checked(old('PH_UPDATE_MANDATORY'))>
                            <label class="form-check-label" for="new_PH_UPDATE_MANDATORY" style="font-size:var(--font-size-xs);"
                                   title="{{ __('admin.references.competence_hierarchy.cb_mandatory_help') }}">{{ __('admin.references.competence_hierarchy.cb_mandatory') }}</label>
                        </div>
                    </div>
                    <div class="col-auto ms-auto">
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
            <div class="ob-widget-card-title"><i class="fas fa-stream me-2"></i>{{ __('admin.references.competence_hierarchy.list_title', ['count' => $hierarchies->count()]) }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px;">{{ __('admin.references.col_code') }}</th>
                        <th style="width:200px;">{{ __('admin.references.col_description') }}</th>
                        <th class="text-center" style="width:60px;" title="{{ __('admin.references.competence_hierarchy.cb_hide_help') }}">{{ __('admin.references.competence_hierarchy.col_hide') }}</th>
                        <th class="text-center" style="width:70px;" title="{{ __('admin.references.competence_hierarchy.cb_expiry_help') }}">{{ __('admin.references.competence_hierarchy.col_expiry') }}</th>
                        <th class="text-center" style="width:80px;" title="{{ __('admin.references.competence_hierarchy.cb_mandatory_help') }}">{{ __('admin.references.competence_hierarchy.col_mandatory') }}</th>
                        <th>{{ __('admin.references.competence_hierarchy.col_competences') }}</th>
                        <th style="width:90px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($hierarchies as $h)
                    @php($rows = $members[$h->PH_CODE] ?? collect())
                    <tr>
                        <td class="font-monospace fw-semibold" style="font-size:var(--font-size-sm);">{{ $h->PH_CODE }}</td>
                        <td style="font-size:var(--font-size-sm);">{{ $h->PH_NAME }}</td>
                        <td class="text-center">
                            @if($h->PH_HIDE_LOWER)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center">
                            @if($h->PH_UPDATE_LOWER_EXPIRY)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td class="text-center">
                            @if($h->PH_UPDATE_MANDATORY)<i class="fas fa-check text-success"></i>@endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @forelse($rows as $m)
                                    <form method="POST" action="{{ route('admin.references.competence-hierarchy.member.remove', [$h->PH_CODE, $m->PS_ID]) }}"
                                          class="ob-badge ob-badge-int d-inline-flex align-items-center gap-1 m-0"
                                          title="{{ $m->DESCRIPTION }}"
                                          onsubmit="return confirm('{{ __('admin.references.competence_hierarchy.member_remove_confirm') }}')">
                                        @csrf @method('DELETE')
                                        <span class="text-muted">{{ __('admin.references.competence_hierarchy.member_level_short') }}{{ $m->PH_LEVEL }}</span>
                                        <span>{{ $m->TYPE }}</span>
                                        <button type="submit" class="btn-close btn-close-white" style="font-size:0.5rem;line-height:1;"></button>
                                    </form>
                                @empty
                                    <span class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.references.competence_hierarchy.members_none') }}</span>
                                @endforelse
                            </div>
                            @if($available->isNotEmpty())
                                <form method="POST" action="{{ route('admin.references.competence-hierarchy.member.add', $h->PH_CODE) }}"
                                      class="d-flex gap-1 align-items-center">
                                    @csrf
                                    <select name="PS_ID" class="form-select form-select-sm" required style="max-width:280px;font-size:var(--font-size-xs);">
                                        <option value="">{{ __('admin.references.competence_hierarchy.member_choose') }}</option>
                                        @foreach($available->groupBy('EQ_NOM') as $team => $items)
                                            <optgroup label="{{ $team }}">
                                                @foreach($items as $p)
                                                    <option value="{{ $p->PS_ID }}">{{ $p->TYPE }} — {{ $p->DESCRIPTION }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <input type="number" name="PH_LEVEL" value="1" min="1" max="99" required
                                           class="form-control form-control-sm" style="width:64px;font-size:var(--font-size-xs);"
                                           title="{{ __('admin.references.competence_hierarchy.member_level') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.references.competence_hierarchy.all_assigned') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#editHierarchy{{ $loop->index }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.references.competence-hierarchy.destroy', $h->PH_CODE) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('{{ __('admin.references.competence_hierarchy.delete_confirm', ['code' => addslashes($h->PH_CODE)]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Edit modal --}}
                    <div class="modal fade" id="editHierarchy{{ $loop->index }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.references.competence-hierarchy.update', $h->PH_CODE) }}">
                                    @csrf @method('PATCH')
                                    <div class="modal-header">
                                        <h6 class="modal-title">{{ __('admin.references.competence_hierarchy.modal_edit_title', ['code' => $h->PH_CODE]) }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-auto">
                                                <label class="form-label form-label-sm">{{ __('admin.references.competence_hierarchy.form_code') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="PH_CODE" value="{{ $h->PH_CODE }}"
                                                       class="form-control form-control-sm text-uppercase" maxlength="15" required style="width:130px;"
                                                       pattern="[A-Za-z0-9_]+" title="{{ __('admin.references.code_pattern_title') }}">
                                            </div>
                                            <div class="col">
                                                <label class="form-label form-label-sm">{{ __('admin.references.competence_hierarchy.form_name') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="PH_NAME" value="{{ $h->PH_NAME }}"
                                                       class="form-control form-control-sm" maxlength="30" required>
                                            </div>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="PH_HIDE_LOWER" id="edit_{{ $loop->index }}_hide" value="1" @checked($h->PH_HIDE_LOWER)>
                                            <label class="form-check-label" for="edit_{{ $loop->index }}_hide" style="font-size:var(--font-size-sm);">{{ __('admin.references.competence_hierarchy.cb_hide') }}</label>
                                            <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.references.competence_hierarchy.cb_hide_help') }}</div>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="PH_UPDATE_LOWER_EXPIRY" id="edit_{{ $loop->index }}_expiry" value="1" @checked($h->PH_UPDATE_LOWER_EXPIRY)>
                                            <label class="form-check-label" for="edit_{{ $loop->index }}_expiry" style="font-size:var(--font-size-sm);">{{ __('admin.references.competence_hierarchy.cb_expiry') }}</label>
                                            <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.references.competence_hierarchy.cb_expiry_help') }}</div>
                                        </div>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="PH_UPDATE_MANDATORY" id="edit_{{ $loop->index }}_mandatory" value="1" @checked($h->PH_UPDATE_MANDATORY)>
                                            <label class="form-check-label" for="edit_{{ $loop->index }}_mandatory" style="font-size:var(--font-size-sm);">{{ __('admin.references.competence_hierarchy.cb_mandatory') }}</label>
                                            <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.references.competence_hierarchy.cb_mandatory_help') }}</div>
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
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('admin.references.competence_hierarchy.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
