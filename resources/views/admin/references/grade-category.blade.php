@extends('layout.app')

@section('title', __('admin.references.grade_category.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')],
    ['label' => __('admin.references.title'), 'url' => route('admin.references')],
    ['label' => __('admin.references.grade_category.title')],
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
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>{{ __('admin.references.grade_category.new_title') }}</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.grade-category.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3" style="font-size:var(--font-size-sm);">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label form-label-sm">{{ __('admin.references.col_code') }} <span class="text-danger">*</span></label>
                        <input type="text" name="CG_CODE" value="{{ old('CG_CODE') }}"
                               class="form-control form-control-sm text-uppercase" maxlength="10" required
                               style="width:100px;" placeholder="OFF">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">{{ __('admin.references.col_description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="CG_DESCRIPTION" value="{{ old('CG_DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="50" required
                               placeholder="{{ __('admin.references.grade_category.ph_desc') }}">
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
            <div class="ob-widget-card-title"><i class="fas fa-layer-group me-2"></i>{{ __('admin.references.grade_category.list_title', ['count' => $categories->count()]) }}</div>
            <a href="{{ route('admin.references.grade') }}" class="btn btn-sm btn-light">
                <i class="fas fa-medal me-1"></i> {{ __('admin.references.grade_category.see_grades') }}
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:100px;">{{ __('admin.references.col_code') }}</th>
                        <th>{{ __('admin.references.col_description') }}</th>
                        <th class="text-center" style="width:90px;">{{ __('admin.references.grade_category.col_grades') }}</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="align-middle font-monospace fw-semibold" style="font-size:var(--font-size-sm);">{{ $cat->CG_CODE }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.references.grade-category.update', $cat->CG_CODE) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="CG_DESCRIPTION" value="{{ $cat->CG_DESCRIPTION }}"
                                       class="form-control form-control-sm" maxlength="50" required style="min-width:200px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center align-middle">
                            <span class="ob-badge ob-badge-int">{{ $gradeCounts[$cat->CG_CODE] ?? 0 }}</span>
                        </td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.references.grade-category.destroy', $cat->CG_CODE) }}"
                                  onsubmit="return confirm('{{ __('admin.references.grade_category.delete_confirm', ['code' => addslashes($cat->CG_CODE)]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        {{ ($gradeCounts[$cat->CG_CODE] ?? 0) > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('admin.references.grade_category.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
