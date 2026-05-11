@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: intervention_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">InterventionEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.intervention_edit.update', $itemKey) : route('legacy_migrated.intervention_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="important" class="form-label">Important</label>
            <input type="text" id="important" name="important" class="form-control" value="{{ old('important', $item?->important) }}">
            @error('important')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_debut" class="form-label">Date Debut</label>
            <input type="text" id="date_debut" name="date_debut" class="form-control" value="{{ old('date_debut', $item?->date_debut) }}">
            @error('date_debut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="heure_debut" class="form-label">Heure Debut</label>
            <input type="text" id="heure_debut" name="heure_debut" class="form-control" value="{{ old('heure_debut', $item?->heure_debut) }}">
            @error('heure_debut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="heure_sll" class="form-label">Heure Sll</label>
            <input type="text" id="heure_sll" name="heure_sll" class="form-control" value="{{ old('heure_sll', $item?->heure_sll) }}">
            @error('heure_sll')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="imprimer" class="form-label">Imprimer</label>
            <input type="text" id="imprimer" name="imprimer" class="form-control" value="{{ old('imprimer', $item?->imprimer) }}">
            @error('imprimer')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_fin" class="form-label">Date Fin</label>
            <input type="text" id="date_fin" name="date_fin" class="form-control" value="{{ old('date_fin', $item?->date_fin) }}">
            @error('date_fin')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="heure_fin" class="form-label">Heure Fin</label>
            <input type="text" id="heure_fin" name="heure_fin" class="form-control" value="{{ old('heure_fin', $item?->heure_fin) }}">
            @error('heure_fin')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="comptage" class="form-label">Comptage</label>
            <input type="text" id="comptage" name="comptage" class="form-control" value="{{ old('comptage', $item?->comptage) }}">
            @error('comptage')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $item?->address) }}">
            @error('address')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="eq_" class="form-label">Eq </label>
            <input type="text" id="eq_" name="eq_" class="form-control" value="{{ old('eq_', $item?->eq_) }}">
            @error('eq_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="commentaire" class="form-label">Commentaire</label>
            <textarea id="commentaire" name="commentaire" class="form-control" rows="4">{{ old('commentaire', $item?->commentaire) }}</textarea>
            @error('commentaire')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="responsable" class="form-label">Responsable</label>
            <textarea id="responsable" name="responsable" class="form-control" rows="4">{{ old('responsable', $item?->responsable) }}</textarea>
            @error('responsable')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="action" class="form-label">Action</label>
            <input type="text" id="action" name="action" class="form-control" value="{{ old('action', $item?->action) }}">
            @error('action')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="numinter" class="form-label">Numinter</label>
            <input type="text" id="numinter" name="numinter" class="form-control" value="{{ old('numinter', $item?->numinter) }}">
            @error('numinter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="evenement" class="form-label">Evenement</label>
            <input type="text" id="evenement" name="evenement" class="form-control" value="{{ old('evenement', $item?->evenement) }}">
            @error('evenement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="modeinter" class="form-label">Modeinter</label>
            <input type="text" id="modeinter" name="modeinter" class="form-control" value="{{ old('modeinter', $item?->modeinter) }}">
            @error('modeinter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="security" class="form-label">Security</label>
            <input type="text" id="security" name="security" class="form-control" value="{{ old('security', $item?->security) }}">
            @error('security')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" id="type" name="type" class="form-control" value="{{ old('type', $item?->type) }}">
            @error('type')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" id="titre" name="titre" class="form-control" value="{{ old('titre', $item?->titre) }}">
            @error('titre')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="origine" class="form-label">Origine</label>
            <input type="text" id="origine" name="origine" class="form-control" value="{{ old('origine', $item?->origine) }}">
            @error('origine')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.intervention_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
