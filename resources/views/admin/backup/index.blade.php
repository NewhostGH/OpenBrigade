@extends('layout.app')

@section('title', 'Sauvegarde — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Sauvegarde & restauration'],
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
                Crée un dump SQL complet de la base de données (mysqldump). Les {{ $settings->retention_count }} fichiers les plus récents sont conservés.
            </span>
        </div>
    </div>

    {{-- Settings --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-cog me-2"></i>Préférences</div>
        </div>
        <form method="POST" action="{{ route('admin.backup.settings') }}" class="p-3" id="backupSettingsForm">
            @csrf @method('PATCH')

            <div class="row g-4">
                {{-- Automatic backups --}}
                <div class="col-md-6">
                    <h6 class="mb-3"><i class="fas fa-clock me-2"></i>Sauvegardes automatiques</h6>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="auto_enabled" id="auto_enabled" value="1"
                                   class="form-check-input" {{ old('auto_enabled', $settings->auto_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_enabled">Activer les sauvegardes automatiques</label>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-auto">
                            <label class="form-label form-label-sm" for="frequency">Fréquence</label>
                            <select name="frequency" id="frequency" class="form-select form-select-sm">
                                <option value="hourly" {{ old('frequency', $settings->frequency) === 'hourly' ? 'selected' : '' }}>Horaire</option>
                                <option value="daily" {{ old('frequency', $settings->frequency) === 'daily' ? 'selected' : '' }}>Quotidienne</option>
                                <option value="weekly" {{ old('frequency', $settings->frequency) === 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                                <option value="monthly" {{ old('frequency', $settings->frequency) === 'monthly' ? 'selected' : '' }}>Mensuelle</option>
                            </select>
                        </div>
                        <div class="col-auto" data-frequency-option="weekly">
                            <label class="form-label form-label-sm" for="day_of_week">Jour de la semaine</label>
                            <select name="day_of_week" id="day_of_week" class="form-select form-select-sm">
                                @foreach(\App\Models\BackupSetting::DAYS_OF_WEEK as $value => $label)
                                    <option value="{{ $value }}" {{ (int) old('day_of_week', $settings->day_of_week) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto" data-frequency-option="monthly">
                            <label class="form-label form-label-sm" for="day_of_month">Jour du mois</label>
                            <input type="number" name="day_of_month" id="day_of_month" min="1" max="31"
                                   value="{{ old('day_of_month', $settings->day_of_month ?? 1) }}"
                                   class="form-control form-control-sm" style="max-width:90px;">
                        </div>
                        <div class="col-auto" data-frequency-option="hourly daily weekly monthly">
                            <label class="form-label form-label-sm" for="run_time" data-run-time-label>Heure d'exécution</label>
                            <input type="time" name="run_time" id="run_time"
                                   value="{{ old('run_time', \Illuminate\Support\Carbon::parse($settings->run_time)->format('H:i')) }}"
                                   class="form-control form-control-sm" style="max-width:120px;">
                        </div>
                        <div class="col-auto">
                            <label class="form-label form-label-sm" for="start_date">À partir du</label>
                            <input type="date" name="start_date" id="start_date"
                                   value="{{ old('start_date', $settings->start_date?->format('Y-m-d')) }}"
                                   class="form-control form-control-sm" style="max-width:160px;">
                        </div>
                    </div>

                    <p class="text-muted mt-3 mb-0" style="font-size:var(--font-size-xs);" data-frequency-hint="hourly">
                        Pour une fréquence horaire, seule la <strong>minute</strong> de l'heure d'exécution est prise en compte (sauvegarde déclenchée chaque heure à cette minute).
                    </p>
                    <p class="text-muted mt-1 mb-0" style="font-size:var(--font-size-xs);">
                        Dernière sauvegarde automatique :
                        {{ $settings->last_auto_backup_at?->format('d/m/Y H:i') ?? 'jamais' }}
                    </p>
                </div>

                {{-- Other options --}}
                <div class="col-md-6">
                    <h6 class="mb-3"><i class="fas fa-sliders-h me-2"></i>Autres options</h6>

                    <div class="row g-3 align-items-end">
                        <div class="col-auto">
                            <label class="form-label form-label-sm" for="retention_count">Sauvegardes à conserver</label>
                            <input type="number" name="retention_count" id="retention_count" min="1" max="365"
                                   value="{{ old('retention_count', $settings->retention_count) }}"
                                   class="form-control form-control-sm" style="max-width:120px;">
                        </div>
                        <div class="col-auto">
                            <label class="form-label form-label-sm" for="naming_pattern">Convention de nommage</label>
                            <select name="naming_pattern" id="naming_pattern" class="form-select form-select-sm">
                                @foreach(\App\Models\BackupSetting::NAMING_PATTERNS as $pattern => $example)
                                    <option value="{{ $pattern }}" data-example="{{ $example }}.sql"
                                            {{ old('naming_pattern', $settings->naming_pattern) === $pattern ? 'selected' : '' }}>
                                        {{ $pattern }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size:var(--font-size-xs);">
                        Exemple : <span class="font-monospace" id="namingPatternExample"></span>
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-save me-1"></i>Enregistrer
                </button>
            </div>
        </form>
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

(function () {
    var form = document.getElementById('backupSettingsForm');
    var frequencySelect = form.querySelector('#frequency');
    var optionEls = form.querySelectorAll('[data-frequency-option]');
    var hintEls = form.querySelectorAll('[data-frequency-hint]');

    function syncFrequencyOptions() {
        var freq = frequencySelect.value;
        optionEls.forEach(function (el) {
            var shown = el.dataset.frequencyOption.split(' ').indexOf(freq) !== -1;
            el.style.display = shown ? '' : 'none';
        });
        hintEls.forEach(function (el) {
            el.style.display = el.dataset.frequencyHint === freq ? '' : 'none';
        });
    }

    frequencySelect.addEventListener('change', syncFrequencyOptions);
    syncFrequencyOptions();

    var namingSelect = form.querySelector('#naming_pattern');
    var namingExample = document.getElementById('namingPatternExample');

    function syncNamingExample() {
        var selected = namingSelect.options[namingSelect.selectedIndex];
        namingExample.textContent = selected ? selected.dataset.example : '';
    }

    namingSelect.addEventListener('change', syncNamingExample);
    syncNamingExample();
})();
</script>
@endpush

@endsection
