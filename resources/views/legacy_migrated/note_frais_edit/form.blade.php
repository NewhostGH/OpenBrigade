@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: note_frais_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">NoteFraisEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.note_frais_edit.update', $itemKey) : route('legacy_migrated.note_frais_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">

<input type="hidden" id="csrf_token_note" name="csrf_token_note" value="{{ old('csrf_token_note', $item?->csrf_token_note) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">


        <div class="mb-3">
            <label for="don" class="form-label">Don</label>
            <input type="text" id="don" name="don" class="form-control" value="{{ old('don', $item?->don) }}">
            @error('don')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="frais_dep" name="frais_dep" value="{{ old('frais_dep', $item?->frais_dep) }}">


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

<input type="hidden" id="motif" name="motif" value="{{ old('motif', $item?->motif) }}">


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

<input type="hidden" id="syndicate" name="syndicate" value="{{ old('syndicate', $item?->syndicate) }}">

<input type="hidden" id="verified" name="verified" value="{{ old('verified', $item?->verified) }}">


        <div class="mb-3">
            <label for="userfile" class="form-label">Userfile</label>
            <input type="text" id="userfile" name="userfile" class="form-control" value="{{ old('userfile', $item?->userfile) }}">
            @error('userfile')
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

<input type="hidden" id="update_detail" name="update_detail" value="{{ old('update_detail', $item?->update_detail) }}">


        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="text" id="date" name="date" class="form-control" value="{{ old('date', $item?->date) }}">
            @error('date')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="quantite" class="form-label">Quantite</label>
            <input type="text" id="quantite" name="quantite" class="form-control" value="{{ old('quantite', $item?->quantite) }}">
            @error('quantite')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="montant" class="form-label">Montant</label>
            <input type="text" id="montant" name="montant" class="form-control" value="{{ old('montant', $item?->montant) }}">
            @error('montant')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="lieu" class="form-label">Lieu</label>
            <input type="text" id="lieu" name="lieu" class="form-control" value="{{ old('lieu', $item?->lieu) }}">
            @error('lieu')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.note_frais_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
