@extends('layout.app')

@section('title', 'Icônes de grades — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Icônes de grades'],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-medal me-2"></i>Icônes de grades ({{ $grades->count() }})</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted);">
                PNG/JPG recommandé — 48×48 px minimum
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">Icône</th>
                        <th style="width:80px;">Code</th>
                        <th>Description</th>
                        <th style="width:120px;">Catégorie</th>
                        <th style="width:60px;">Niv.</th>
                        <th style="width:280px;">Changer l'icône</th>
                    </tr>
                </thead>
                <tbody>
                @php $prevCat = null; @endphp
                @foreach($grades as $g)
                    @if($g->G_CATEGORY !== $prevCat)
                        <tr class="table-secondary">
                            <td colspan="6" class="py-1 fw-semibold" style="font-size:var(--font-size-xs);letter-spacing:.05em;">
                                {{ $g->cat_label ?: $g->G_CATEGORY }}
                            </td>
                        </tr>
                        @php $prevCat = $g->G_CATEGORY; @endphp
                    @endif
                    <tr>
                        <td class="align-middle text-center">
                            @php
                                $hasIcon = $g->G_ICON && str_starts_with($g->G_ICON, 'grades/') && Storage::disk('public')->exists($g->G_ICON);
                                $legacyIcon = $g->G_ICON && !str_starts_with($g->G_ICON, 'grades/') && $g->G_ICON !== 'images/user-specific/DEFAULT.png';
                            @endphp
                            @if($hasIcon)
                                <img src="{{ Storage::url($g->G_ICON) }}" alt="{{ $g->G_GRADE }}"
                                     style="width:36px;height:36px;object-fit:contain;">
                            @elseif($legacyIcon)
                                {{-- TODO: Migrate code — legacy grade icon path --}}
                                <span class="text-muted" title="{{ $g->G_ICON }}" style="font-size:var(--font-size-xs);">
                                    <i class="fas fa-image"></i>
                                </span>
                            @else
                                <i class="fas fa-medal text-muted"></i>
                            @endif
                        </td>
                        <td class="font-monospace align-middle fw-semibold" style="font-size:var(--font-size-sm);">
                            {{ $g->G_GRADE }}
                        </td>
                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $g->G_DESCRIPTION }}</td>
                        <td class="align-middle" style="font-size:var(--font-size-xs);color:var(--text-muted);">{{ $g->cat_label }}</td>
                        <td class="align-middle text-center" style="font-size:var(--font-size-sm);">{{ $g->G_LEVEL }}</td>
                        <td class="align-middle">
                            <div class="d-flex gap-2 align-items-center">
                                <form method="POST"
                                      action="{{ route('admin.references.grade.icon.upload', $g->G_GRADE) }}"
                                      enctype="multipart/form-data"
                                      class="d-flex gap-1 align-items-center">
                                    @csrf
                                    <input type="file" name="icon" class="form-control form-control-sm"
                                           accept="image/png,image/jpeg,image/gif,image/webp"
                                           style="max-width:160px;">
                                    <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                        <i class="fas fa-upload"></i>
                                    </button>
                                </form>
                                @if($hasIcon)
                                    <form method="POST" action="{{ route('admin.references.grade.icon.destroy', $g->G_GRADE) }}"
                                          onsubmit="return confirm('Supprimer l\'icône de {{ addslashes($g->G_GRADE) }} ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
