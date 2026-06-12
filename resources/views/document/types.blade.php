@extends('layout.app')

@section('title', 'Types de documents — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Documents', 'url' => route('document.index')],
    ['label' => 'Types de documents'],
]"/>

<x-ob-toolbar title="Types de documents" :total="$types->count()"
    :columns="$columns" table-id="docTypeTable">
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#typeCreateModal">
        <i class="fas fa-plus me-1"></i> Nouveau type
    </button>
</x-ob-toolbar>

<div class="row g-3 mx-1">
    <div class="col-lg-8">
        <x-ob-commandbar table-id="docTypeTable" :total="$types->count()" total-label="type">
            <x-ob-table :columns="$columns" :items="$types" table-id="docTypeTable"
                empty-text="Aucun type de document."/>
        </x-ob-commandbar>
    </div>

    {{-- Reference: per-document security levels (read-only) --}}
    <div class="col-lg-4">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-shield-halved me-2"></i>Niveaux de visibilité</div>
            </div>
            <div class="ob-widget-card-body p-0">
                <ul class="list-group list-group-flush" style="font-size:var(--font-size-sm);">
                    @foreach ($securities as $s)
                        <li class="list-group-item">{{ $s->DS_LIBELLE }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="p-2 text-muted" style="font-size:var(--font-size-xs);">
                Ces niveaux (référence) s'appliquent par document à l'ajout.
            </div>
        </div>
    </div>
</div>

{{-- Create type --}}
<div class="modal fade" id="typeCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('document.types.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nouveau type de document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="typeCreateCode">Code (5 car. max)</label>
                    <input type="text" id="typeCreateCode" name="code" class="form-control" maxlength="5" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="typeCreateLibelle">Libellé</label>
                    <input type="text" id="typeCreateLibelle" name="libelle" class="form-control" maxlength="50" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="typeCreateSecurity">Visible si la personne a le droit</label>
                    <select id="typeCreateSecurity" name="security" class="form-select">
                        <option value="0">Public (tout le monde)</option>
                        @foreach ($features as $f)
                            <option value="{{ $f->F_ID }}">{{ $f->F_LIBELLE }} (#{{ $f->F_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="typeCreateSyndicate" name="syndicate" value="1" class="form-check-input">
                    <label class="form-check-label" for="typeCreateSyndicate">Réservé au syndicat</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-sm btn-primary">Créer</button>
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
                <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Modifier le type <span id="typeEditCode" class="text-muted"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="typeEditLibelle">Libellé</label>
                    <input type="text" id="typeEditLibelle" name="libelle" class="form-control" maxlength="50" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="typeEditSecurity">Visible si la personne a le droit</label>
                    <select id="typeEditSecurity" name="security" class="form-select">
                        <option value="0">Public (tout le monde)</option>
                        @foreach ($features as $f)
                            <option value="{{ $f->F_ID }}">{{ $f->F_LIBELLE }} (#{{ $f->F_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="typeEditSyndicate" name="syndicate" value="1" class="form-check-input">
                    <label class="form-check-label" for="typeEditSyndicate">Réservé au syndicat</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
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
            if (!window.confirm('Supprimer ce type de document ?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
