@extends('layout.app')

@section('title', __('organization.page_sections') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('organization.bc_organisation')],
    ['label' => __('organization.bc_sections')],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-layer-group me-2"></i>{{ __('organization.page_sections') }}</div>
            <div class="ob-widget-card-actions">
                <a href="{{ route('organization.org-chart') }}" class="btn btn-sm btn-outline-secondary me-1">
                    <i class="fas fa-project-diagram me-1"></i>{{ __('organization.bc_org_chart') }}
                </a>
                <a href="{{ route('organization.sections.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>{{ __('organization.new_section') }}
                </a>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            <div class="table-responsive">
                <table class="ob-table">
                    <thead>
                        <tr>
                            <th>{{ __('organization.col_code') }}</th>
                            <th>{{ __('organization.col_name') }}</th>
                            <th>{{ __('organization.col_parent_section') }}</th>
                            <th>{{ __('organization.col_city') }}</th>
                            <th class="text-center">{{ __('organization.col_members') }}</th>
                            <th class="text-center">{{ __('organization.col_order') }}</th>
                            <th class="text-center">{{ __('organization.col_status') }}</th>
                            <th style="width:52px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($sections as $s)
                        <tr data-href="{{ route('organization.sections.show', $s->S_ID) }}" style="cursor:pointer;">
                            <td class="font-monospace fw-semibold">{{ $s->S_CODE }}</td>
                            <td>{{ $s->S_DESCRIPTION }}</td>
                            <td class="text-muted" style="font-size:var(--font-size-xs);">
                                {{ $s->parent_name ? ($s->parent_code . ' — ' . $s->parent_name) : '—' }}
                            </td>
                            <td>{{ $s->S_CITY ?: '—' }}</td>
                            <td class="text-center">
                                <span class="ob-badge ob-badge-int">{{ (int) ($counts[$s->S_ID] ?? 0) }}</span>
                            </td>
                            <td class="text-center">{{ $s->S_ORDER }}</td>
                            <td class="text-center">
                                @if ($s->S_INACTIVE)
                                    <span class="ob-badge ob-badge-archive">{{ __('organization.status_inactive') }}</span>
                                @else
                                    <span class="ob-badge ob-badge-actif">{{ __('organization.status_active') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('organization.sections.show', $s->S_ID) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted p-3">{{ __('organization.no_sections') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.ob-table tr[data-href]').forEach(row => {
    row.addEventListener('click', () => { window.location = row.dataset.href; });
});
</script>
@endpush

@endsection
