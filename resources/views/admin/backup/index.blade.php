@extends('layout.app')

@section('title', 'Sauvegarde — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Sauvegarde &amp; restauration'],
]"/>

<div class="mx-3 mt-3">

    {{-- Actions --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-database me-2"></i>Créer une sauvegarde</div>
        </div>
        <div class="p-3 d-flex align-items-center gap-3 flex-wrap">
            <form method="POST" action="{{ route('admin.backup.store') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i>Nouvelle sauvegarde maintenant
                </button>
            </form>
            <span class="text-muted" style="font-size:var(--font-size-xs);">
                Crée un dump SQL complet de la base de données (mysqldump). Les 30 fichiers les plus récents sont conservés.
            </span>
        </div>
    </div>

    {{-- Backup list --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-history me-2"></i>Sauvegardes disponibles ({{ $files->count() }})
            </div>
        </div>
        @if($files->isEmpty())
            <div class="p-4 text-center text-muted">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                Aucune sauvegarde. Créez-en une ci-dessus.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fichier</th>
                            <th style="width:120px;" class="text-end">Taille</th>
                            <th style="width:170px;">Date</th>
                            <th style="width:180px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($files as $f)
                        <tr>
                            <td class="align-middle font-monospace" style="font-size:var(--font-size-xs);">
                                {{ $f['filename'] }}
                            </td>
                            <td class="align-middle text-end" style="font-size:var(--font-size-sm);">
                                {{ $f['size_kb'] }} Ko
                            </td>
                            <td class="align-middle" style="font-size:var(--font-size-sm);">
                                {{ $f['date']->format('d/m/Y H:i') }}
                            </td>
                            <td class="align-middle text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.backup.download', $f['filename']) }}"
                                       class="btn btn-sm btn-outline-primary" title="Télécharger">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            title="Restaurer"
                                            data-bs-toggle="modal"
                                            data-bs-target="#restoreModal"
                                            data-filename="{{ $f['filename'] }}"
                                            data-date="{{ $f['date']->format('d/m/Y H:i') }}">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.backup.destroy', $f['filename']) }}"
                                          onsubmit="return confirm('Supprimer {{ addslashes($f['filename']) }} ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Restore warning --}}
    <div class="ob-widget-card" style="border-color:var(--card-danger-border) !important;">
        <div class="ob-widget-card-header" style="background:var(--card-danger-bg);">
            <div class="ob-widget-card-title" style="color:var(--card-danger-title);">
                <i class="fas fa-exclamation-triangle me-2"></i>Restauration
            </div>
        </div>
        <div class="p-3" style="font-size:var(--font-size-sm);">
            <p class="mb-1"><strong>La restauration écrase intégralement la base de données actuelle.</strong>
            Toutes les données saisies après la date de la sauvegarde seront perdues.</p>
            <p class="mb-0 text-muted">Utilisez le bouton <i class="fas fa-undo"></i> sur le fichier souhaité. Une confirmation par saisie manuelle est exigée.</p>
        </div>
    </div>
</div>

{{-- Restore modal --}}
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Confirmer la restauration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.backup.restore') }}" id="restoreForm">
                @csrf
                <div class="modal-body">
                    <p>Vous allez restaurer la base depuis&nbsp;:</p>
                    <p class="font-monospace fw-semibold" id="restoreFilename" style="font-size:var(--font-size-xs);"></p>
                    <p class="text-danger fw-semibold">Cette opération est irréversible. Toutes les données actuelles seront remplacées.</p>
                    <div class="mb-3">
                        <label class="form-label form-label-sm">
                            Tapez <strong>CONFIRMER</strong> pour valider :
                        </label>
                        <input type="text" name="confirm" class="form-control form-control-sm"
                               placeholder="CONFIRMER" autocomplete="off">
                    </div>
                    <input type="hidden" name="filename" id="restoreFilenameInput">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo me-1"></i>Restaurer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('restoreModal').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('restoreFilename').textContent = btn.dataset.filename + ' — ' + btn.dataset.date;
    document.getElementById('restoreFilenameInput').value = btn.dataset.filename;
    this.querySelector('[name="confirm"]').value = '';
});
</script>
@endpush

@endsection
