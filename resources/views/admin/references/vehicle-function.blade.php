@extends('layout.app')

@section('title', __('admin.references.vehicle_function.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')],
    ['label' => __('admin.references.title'), 'url' => route('admin.references')],
    ['label' => __('admin.references.vehicle_function.title')],
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
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>{{ __('admin.references.vehicle_function.new_title') }}</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.vehicle-function.store') }}">
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
                        <label class="form-label form-label-sm">{{ __('admin.references.vehicle_function.col_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="TFV_NAME" value="{{ old('TFV_NAME') }}"
                               class="form-control form-control-sm" maxlength="50" required
                               style="width:180px;" placeholder="{{ __('admin.references.vehicle_function.ph_name') }}">
                    </div>
                    <div class="col">
                        <label class="form-label form-label-sm">{{ __('admin.references.vehicle_function.col_desc') }}</label>
                        <input type="text" name="TFV_DESCRIPTION" value="{{ old('TFV_DESCRIPTION') }}"
                               class="form-control form-control-sm" maxlength="100">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">{{ __('admin.references.col_order') }}</label>
                        <input type="number" name="TFV_ORDER" value="{{ old('TFV_ORDER', 10) }}"
                               class="form-control form-control-sm" min="0" max="9999" style="width:80px;" required>
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
            <div class="ob-widget-card-title"><i class="fas fa-truck me-2"></i>{{ __('admin.references.vehicle_function.list_title', ['count' => $items->count()]) }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('admin.references.vehicle_function.col_name') }}</th>
                        <th>{{ __('admin.references.vehicle_function.col_desc') }}</th>
                        <th style="width:80px;">{{ __('admin.references.col_order') }}</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.references.vehicle-function.update', $item->TFV_ID) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="TFV_NAME" value="{{ $item->TFV_NAME }}"
                                       class="form-control form-control-sm" maxlength="50" required style="min-width:160px;">
                                <input type="text" name="TFV_DESCRIPTION" value="{{ $item->TFV_DESCRIPTION }}"
                                       class="form-control form-control-sm" maxlength="100" style="min-width:200px;"
                                       placeholder="{{ __('admin.references.vehicle_function.ph_desc') }}">
                                <input type="number" name="TFV_ORDER" value="{{ $item->TFV_ORDER }}"
                                       class="form-control form-control-sm" min="0" max="9999" style="width:70px;" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-xs);">{{ $item->TFV_DESCRIPTION }}</td>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-sm);">{{ $item->TFV_ORDER }}</td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.references.vehicle-function.destroy', $item->TFV_ID) }}"
                                  onsubmit="return confirm('{{ __('admin.references.vehicle_function.delete_confirm', ['name' => addslashes($item->TFV_NAME)]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('admin.references.vehicle_function.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
