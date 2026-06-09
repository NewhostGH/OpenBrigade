@extends('layout.app')

@section('title', 'Sections — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Organisation'],
    ['label' => 'Sections'],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-layer-group me-2"></i>Sections</div>
            <div class="ob-widget-card-actions">
                <a href="{{ route('organisation.index') }}" class="btn btn-sm btn-outline-secondary me-1">
                    <i class="fas fa-project-diagram me-1"></i>Organigramme
                </a>
                <a href="{{ route('organisation.sections.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Nouvelle section
                </a>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Section parente</th>
                            <th>Ville</th>
                            <th class="text-center">Membres</th>
                            <th class="text-center">Ordre</th>
                            <th class="text-center">État</th>
                            <th style="width:110px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($sections as $s)
                        <tr>
                            <td class="align-middle font-monospace fw-semibold" style="font-size:var(--font-size-sm);">{{ $s->S_CODE }}</td>
                            <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $s->S_DESCRIPTION }}</td>
                            <td class="align-middle text-muted" style="font-size:var(--font-size-xs);">
                                {{ $s->parent_name ? ($s->parent_code . ' — ' . $s->parent_name) : '—' }}
                            </td>
                            <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $s->S_CITY ?: '—' }}</td>
                            <td class="text-center align-middle">
                                <span class="ob-badge ob-badge-int">{{ (int) ($counts[$s->S_ID] ?? 0) }}</span>
                            </td>
                            <td class="text-center align-middle" style="font-size:var(--font-size-sm);">{{ $s->S_ORDER }}</td>
                            <td class="text-center align-middle">
                                @if ($s->S_INACTIVE)
                                    <span class="ob-badge ob-badge-archive">Inactive</span>
                                @else
                                    <span class="ob-badge ob-badge-actif">Active</span>
                                @endif
                            </td>
                            <td class="text-end align-middle">
                                <a href="{{ route('organisation.sections.edit', $s->S_ID) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('organisation.sections.destroy', $s->S_ID) }}"
                                      class="d-inline" onsubmit="return confirm('Supprimer la section {{ $s->S_CODE }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted p-3">Aucune section.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
