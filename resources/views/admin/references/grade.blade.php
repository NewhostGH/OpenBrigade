@extends('layout.app')

@section('title', __('admin.references.grade.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')],
    ['label' => __('admin.references.title'), 'url' => route('admin.references')],
    ['label' => __('admin.references.grade.title')],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card">
        <div class="ob-widget-card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div class="ob-widget-card-title"><i class="fas fa-medal me-2"></i>{{ __('admin.references.grade.list_title', ['count' => $grades->count()]) }}</div>

            <div class="d-flex flex-wrap gap-2 align-items-center">
                {{-- Filters --}}
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <select name="category" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width:200px;">
                        <option value="">{{ __('admin.references.grade.filter_all_categories') }}</option>
                        @foreach($categories as $c)
                            @if($c->CG_CODE !== 'ALL')
                                <option value="{{ $c->CG_CODE }}" @selected($catFilter === $c->CG_CODE)>{{ $c->CG_DESCRIPTION }}</option>
                            @endif
                        @endforeach
                    </select>
                    <select name="active" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width:130px;">
                        <option value="all" @selected($activeFilter === 'all')>{{ __('admin.references.grade.filter_active_all') }}</option>
                        <option value="1" @selected($activeFilter === '1')>{{ __('admin.references.grade.filter_active_yes') }}</option>
                        <option value="0" @selected($activeFilter === '0')>{{ __('admin.references.grade.filter_active_no') }}</option>
                    </select>
                </form>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#gradeModal" onclick="obGradeModal(null)">
                    <i class="fas fa-plus me-1"></i>{{ __('admin.references.grade.add') }}
                </button>
            </div>
        </div>

        <div class="ob-widget-card-body pb-0">
            <p class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                <i class="fas fa-arrows-up-down me-1"></i>{{ __('admin.references.grade.reorder_hint') }}
            </p>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px;"></th>
                        <th style="width:56px;">{{ __('admin.references.grade.col_icon') }}</th>
                        <th style="width:80px;">{{ __('admin.references.grade.col_code') }}</th>
                        <th>{{ __('admin.references.grade.col_desc') }}</th>
                        <th style="width:60px;" class="text-center">{{ __('admin.references.grade.col_level') }}</th>
                        <th style="width:70px;" class="text-center">{{ __('admin.references.grade.col_members') }}</th>
                        <th style="width:60px;" class="text-center">{{ __('admin.references.grade.col_active') }}</th>
                        <th style="width:220px;">{{ __('admin.references.grade.col_change') }}</th>
                        <th style="width:90px;" class="text-center">{{ __('admin.references.grade.col_actions') }}</th>
                    </tr>
                </thead>

                @if($grades->isEmpty())
                    <tbody><tr><td colspan="9" class="text-muted text-center py-3">{{ __('admin.references.grade.none') }}</td></tr></tbody>
                @else
                    @foreach($grades->groupBy('G_CATEGORY') as $cat => $rows)
                        <tbody class="ob-grade-group" data-category="{{ $cat }}">
                            <tr class="table-secondary">
                                <td colspan="9" class="py-1 fw-semibold" style="font-size:var(--font-size-xs);letter-spacing:.05em;">
                                    {{ $rows->first()->cat_label ?: $cat }}
                                </td>
                            </tr>
                            @foreach($rows as $g)
                                @php
                                    $hasIcon = $g->G_ICON && str_starts_with($g->G_ICON, 'grades/') && Storage::disk('public')->exists($g->G_ICON);
                                    $members = (int) ($counts[$g->G_GRADE] ?? 0);
                                @endphp
                                <tr draggable="true" class="ob-grade-row" data-grade="{{ $g->G_GRADE }}">
                                    <td class="text-center text-muted" style="cursor:grab;"><i class="fas fa-grip-vertical"></i></td>
                                    <td class="text-center">
                                        @if($hasIcon)
                                            <img src="{{ Storage::url($g->G_ICON) }}" alt="{{ $g->G_GRADE }}" style="width:32px;height:32px;object-fit:contain;">
                                        @else
                                            <i class="fas fa-medal text-muted"></i>
                                        @endif
                                    </td>
                                    <td class="font-monospace fw-semibold" style="font-size:var(--font-size-sm);">{{ $g->G_GRADE }}</td>
                                    <td style="font-size:var(--font-size-sm);">{{ $g->G_DESCRIPTION }}</td>
                                    <td class="text-center" style="font-size:var(--font-size-sm);">{{ $g->G_LEVEL }}</td>
                                    <td class="text-center">
                                        @if($members > 0)<span class="ob-badge ob-badge-int">{{ $members }}</span>@else<span class="text-muted">—</span>@endif
                                    </td>
                                    <td class="text-center">
                                        @if($g->G_FLAG)<span class="ob-badge ob-badge-actif">{{ __('common.yes') }}</span>@else<span class="ob-badge ob-badge-archive">{{ __('common.no') }}</span>@endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 align-items-center">
                                            <form method="POST" action="{{ route('admin.references.grade.icon.upload', $g->G_GRADE) }}" enctype="multipart/form-data" class="d-flex gap-1 align-items-center">
                                                @csrf
                                                <input type="file" name="icon" class="form-control form-control-sm" accept="image/png,image/jpeg,image/gif,image/webp" style="max-width:140px;">
                                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0"><i class="fas fa-upload"></i></button>
                                            </form>
                                            @if($hasIcon)
                                                <form method="POST" action="{{ route('admin.references.grade.icon.destroy', $g->G_GRADE) }}" onsubmit="return confirm(@js(__('admin.references.grade.delete_confirm', ['grade' => $g->G_GRADE])))">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-image"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal" data-bs-target="#gradeModal"
                                                onclick='obGradeModal(@json($g))'><i class="fas fa-edit"></i></button>
                                        <form method="POST" class="d-inline" action="{{ route('admin.references.grade.destroy', $g->G_GRADE) }}"
                                              onsubmit="return confirm(@js(__('admin.references.grade.delete_grade_confirm')))">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                @endif
            </table>
        </div>
    </div>
</div>

{{-- Create / edit modal --}}
<div class="modal fade" id="gradeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="gradeForm" class="modal-content" action="{{ route('admin.references.grade.store') }}">
            @csrf
            <input type="hidden" name="_method" id="gradeMethod" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="gradeModalTitle">{{ __('admin.references.grade.add') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="gradeCodeWrap">
                    <label for="G_GRADE" class="form-label">{{ __('admin.references.grade.field_code') }}</label>
                    <input type="text" name="G_GRADE" id="G_GRADE" maxlength="6" class="form-control text-uppercase">
                    <div class="form-text">{{ __('admin.references.grade.field_code_hint') }}</div>
                </div>
                <div class="mb-3">
                    <label for="G_DESCRIPTION" class="form-label">{{ __('admin.references.grade.field_desc') }}</label>
                    <input type="text" name="G_DESCRIPTION" id="G_DESCRIPTION" maxlength="100" class="form-control" required>
                </div>
                <div class="row g-2">
                    <div class="col-8 mb-3">
                        <label for="G_CATEGORY" class="form-label">{{ __('admin.references.grade.field_category') }}</label>
                        <select name="G_CATEGORY" id="G_CATEGORY" class="form-select" required>
                            @foreach($categories as $c)
                                <option value="{{ $c->CG_CODE }}">{{ $c->CG_DESCRIPTION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 mb-3">
                        <label for="G_LEVEL" class="form-label">{{ __('admin.references.grade.field_level') }}</label>
                        <input type="number" name="G_LEVEL" id="G_LEVEL" min="0" value="0" class="form-control">
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="G_FLAG" id="G_FLAG" value="1" checked>
                    <label class="form-check-label" for="G_FLAG">{{ __('admin.references.grade.field_active') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const OB_GRADE_STORE = @json(route('admin.references.grade.store'));
    const OB_GRADE_BASE  = @json(url('/admin/references/grade'));
    const OB_GRADE_REORDER = @json(route('admin.references.grade.reorder'));
    const OB_CSRF = document.querySelector('meta[name="csrf-token"]').content;

    function obGradeModal(g) {
        const form = document.getElementById('gradeForm');
        const codeInput = document.getElementById('G_GRADE');
        const codeWrap = document.getElementById('gradeCodeWrap');
        if (g) {
            document.getElementById('gradeModalTitle').textContent = @json(__('admin.references.grade.edit'));
            document.getElementById('gradeMethod').value = 'PATCH';
            form.action = OB_GRADE_BASE + '/' + encodeURIComponent(g.G_GRADE);
            codeInput.value = g.G_GRADE;
            codeInput.disabled = true;              // code immutable on edit
            codeWrap.style.display = 'none';
            document.getElementById('G_DESCRIPTION').value = g.G_DESCRIPTION || '';
            document.getElementById('G_CATEGORY').value = g.G_CATEGORY;
            document.getElementById('G_LEVEL').value = g.G_LEVEL ?? 0;
            document.getElementById('G_FLAG').checked = String(g.G_FLAG) === '1';
        } else {
            document.getElementById('gradeModalTitle').textContent = @json(__('admin.references.grade.add'));
            document.getElementById('gradeMethod').value = 'POST';
            form.action = OB_GRADE_STORE;
            form.reset();
            codeInput.disabled = false;
            codeWrap.style.display = '';
            document.getElementById('G_FLAG').checked = true;
        }
    }

    // Drag-and-drop reorder within a category group.
    document.querySelectorAll('tbody.ob-grade-group').forEach(function (group) {
        let dragged = null;
        group.querySelectorAll('tr.ob-grade-row').forEach(function (row) {
            row.addEventListener('dragstart', () => { dragged = row; row.style.opacity = '0.4'; });
            row.addEventListener('dragend', () => { row.style.opacity = ''; });
            row.addEventListener('dragover', (e) => e.preventDefault());
            row.addEventListener('drop', function (e) {
                e.preventDefault();
                if (!dragged || dragged === row) return;
                const rect = row.getBoundingClientRect();
                const after = (e.clientY - rect.top) > rect.height / 2;
                row.parentNode.insertBefore(dragged, after ? row.nextSibling : row);
                persistOrder(group);
            });
        });
    });

    function persistOrder(group) {
        const order = Array.from(group.querySelectorAll('tr.ob-grade-row')).map(r => r.dataset.grade);
        fetch(OB_GRADE_REORDER, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': OB_CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ category: group.dataset.category, order: order }),
        });
    }
</script>
@endpush

@endsection
