@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_facturation_detail.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementFacturationDetail Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_facturation_detail.update', $itemKey) : route('legacy_migrated.evenement_facturation_detail.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="submit" class="form-label">Submit</label>
            <input type="text" id="submit" name="submit" class="form-control" value="{{ old('submit', $item?->submit) }}">
            @error('submit')
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

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="type" name="type" value="{{ old('type', $item?->type) }}">


        <div class="mb-3">
            <label for="label" class="form-label">Label</label>
            <input type="text" id="label" name="label" class="form-control" value="{{ old('label', $item?->label) }}">
            @error('label')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="commentaire" class="form-label">Commentaire</label>
            <input type="text" id="commentaire" name="commentaire" class="form-control" value="{{ old('commentaire', $item?->commentaire) }}">
            @error('commentaire')
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
            <label for="pu" class="form-label">Pu</label>
            <input type="text" id="pu" name="pu" class="form-control" value="{{ old('pu', $item?->pu) }}">
            @error('pu')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="remise" class="form-label">Remise</label>
            <input type="text" id="remise" name="remise" class="form-control" value="{{ old('remise', $item?->remise) }}">
            @error('remise')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="subtotal" class="form-label">Subtotal</label>
            <input type="text" id="subtotal" name="subtotal" class="form-control" value="{{ old('subtotal', $item?->subtotal) }}">
            @error('subtotal')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="btcopie" class="form-label">Btcopie</label>
            <input type="text" id="btcopie" name="btcopie" class="form-control" value="{{ old('btcopie', $item?->btcopie) }}">
            @error('btcopie')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="retour" class="form-label">Retour</label>
            <input type="text" id="retour" name="retour" class="form-control" value="{{ old('retour', $item?->retour) }}">
            @error('retour')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_facturation_detail.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
