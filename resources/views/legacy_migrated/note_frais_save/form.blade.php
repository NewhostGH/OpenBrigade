@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: note_frais_save.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">NoteFrais Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.note_frais_save.update', $itemKey) : route('legacy_migrated.note_frais_save.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="reject_comment" name="reject_comment" value="{{ old('reject_comment', $item?->reject_comment) }}">


        <div class="mb-3">
            <label for="Retour" class="form-label">Retour</label>
            <input type="text" id="Retour" name="Retour" class="form-control" value="{{ old('Retour', $item?->Retour) }}">
            @error('Retour')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nfid" class="form-label">Nfid</label>
            <input type="text" id="nfid" name="nfid" class="form-control" value="{{ old('nfid', $item?->nfid) }}">
            @error('nfid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <input type="text" id="section" name="section" class="form-control" value="{{ old('section', $item?->section) }}">
            @error('section')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="person" class="form-label">Person</label>
            <input type="text" id="person" name="person" class="form-control" value="{{ old('person', $item?->person) }}">
            @error('person')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="sum" class="form-label">Sum</label>
            <input type="text" id="sum" name="sum" class="form-control" value="{{ old('sum', $item?->sum) }}">
            @error('sum')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="from" class="form-label">From</label>
            <input type="text" id="from" name="from" class="form-control" value="{{ old('from', $item?->from) }}">
            @error('from')
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
            <label for="evenement" class="form-label">Evenement</label>
            <input type="text" id="evenement" name="evenement" class="form-control" value="{{ old('evenement', $item?->evenement) }}">
            @error('evenement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="verified" class="form-label">Verified</label>
            <input type="text" id="verified" name="verified" class="form-control" value="{{ old('verified', $item?->verified) }}">
            @error('verified')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="don" class="form-label">Don</label>
            <input type="text" id="don" name="don" class="form-control" value="{{ old('don', $item?->don) }}">
            @error('don')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="justif_recus" class="form-label">Justif Recus</label>
            <input type="text" id="justif_recus" name="justif_recus" class="form-control" value="{{ old('justif_recus', $item?->justif_recus) }}">
            @error('justif_recus')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="frais_dep" class="form-label">Frais Dep</label>
            <input type="text" id="frais_dep" name="frais_dep" class="form-control" value="{{ old('frais_dep', $item?->frais_dep) }}">
            @error('frais_dep')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="national" class="form-label">National</label>
            <input type="text" id="national" name="national" class="form-control" value="{{ old('national', $item?->national) }}">
            @error('national')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="departemental" class="form-label">Departemental</label>
            <input type="text" id="departemental" name="departemental" class="form-control" value="{{ old('departemental', $item?->departemental) }}">
            @error('departemental')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="motif" class="form-label">Motif</label>
            <input type="text" id="motif" name="motif" class="form-control" value="{{ old('motif', $item?->motif) }}">
            @error('motif')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nfcode1" class="form-label">Nfcode1</label>
            <input type="text" id="nfcode1" name="nfcode1" class="form-control" value="{{ old('nfcode1', $item?->nfcode1) }}">
            @error('nfcode1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nfcode2" class="form-label">Nfcode2</label>
            <input type="text" id="nfcode2" name="nfcode2" class="form-control" value="{{ old('nfcode2', $item?->nfcode2) }}">
            @error('nfcode2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nfcode3" class="form-label">Nfcode3</label>
            <input type="text" id="nfcode3" name="nfcode3" class="form-control" value="{{ old('nfcode3', $item?->nfcode3) }}">
            @error('nfcode3')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.note_frais_save.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
