@extends('layout.app')

@section('title', 'Types de documents — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('document.title'), 'url' => route('document.index')],
    ['label' => __('document.types_page_title')],
]"/>

<x-ob-toolbar title="{{ __('document.types_page_title') }}" :total="$types->count()"
    :columns="$columns" table-id="docTypeTable">
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#typeCreateModal">
        <i class="fas fa-plus me-1"></i> {{ __('document.types_btn_new') }}
    </button>
</x-ob-toolbar>

<div class="mx-1">
    <x-ob-commandbar table-id="docTypeTable" :total="$types->count()" total-label="type">
        <x-ob-table :columns="$columns" :items="$types" table-id="docTypeTable"
            empty-text="{{ __('document.types_empty') }}"/>
    </x-ob-commandbar>
</div>

{{-- Create type --}}
<div class="modal fade" id="typeCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('document.types.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>{{ __('document.modal_create_type_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="typeCreateCode">{{ __('document.type_code_label') }}</label>
                    <input type="text" id="typeCreateCode" name="code" class="form-control" maxlength="5" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="typeCreateLibelle">{{ __('document.type_libelle_label') }}</label>
                    <input type="text" id="typeCreateLibelle" name="libelle" class="form-control" maxlength="50" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="typeCreateSecurity">{{ __('document.type_security_label') }}</label>
                    <select id="typeCreateSecurity" name="security" class="form-select">
                        <option value="0">{{ __('document.type_security_public') }}</option>
                        @foreach ($features as $f)
                            <option value="{{ $f->F_ID }}">{{ $f->F_LIBELLE }} (#{{ $f->F_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="typeCreateSyndicate" name="syndicate" value="1" class="form-check-input">
                    <label class="form-check-label" for="typeCreateSyndicate">{{ __('document.type_syndicate_label') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('common.create') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit type (filled by JS) --}}
<div class="modal fade" id="typeEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="typeEditForm" class="modal-content">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen me-2"></i>{{ __('document.modal_edit_type_title') }} <span id="typeEditCode" class="text-muted"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="typeEditLibelle">{{ __('document.type_libelle_label') }}</label>
                    <input type="text" id="typeEditLibelle" name="libelle" class="form-control" maxlength="50" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="typeEditSecurity">{{ __('document.type_security_label') }}</label>
                    <select id="typeEditSecurity" name="security" class="form-select">
                        <option value="0">{{ __('document.type_security_public') }}</option>
                        @foreach ($features as $f)
                            <option value="{{ $f->F_ID }}">{{ $f->F_LIBELLE }} (#{{ $f->F_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="typeEditSyndicate" name="syndicate" value="1" class="form-check-input">
                    <label class="form-check-label" for="typeEditSyndicate">{{ __('document.type_syndicate_label') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('typeEditModal');
    var form = document.getElementById('typeEditForm');
    var base = "{{ url('/documents/types') }}";

    document.querySelectorAll('[data-type-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.setAttribute('action', base + '/' + encodeURIComponent(btn.dataset.code));
            document.getElementById('typeEditCode').textContent = btn.dataset.code;
            document.getElementById('typeEditLibelle').value = btn.dataset.libelle || '';
            document.getElementById('typeEditSecurity').value = btn.dataset.security || '0';
            document.getElementById('typeEditSyndicate').checked = btn.dataset.syndicate === '1';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    });

    document.querySelectorAll('[data-type-delete]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!window.confirm('{{ __('document.confirm_delete_type') }}')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
