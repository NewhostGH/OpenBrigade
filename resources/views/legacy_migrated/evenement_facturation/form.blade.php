@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_facturation.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementFacturation Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_facturation.update', $itemKey) : route('legacy_migrated.evenement_facturation.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="devisAccepte" class="form-label">Devisaccepte</label>
            <input type="text" id="devisAccepte" name="devisAccepte" class="form-control" value="{{ old('devisAccepte', $item?->devisAccepte) }}">
            @error('devisAccepte')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisDate" class="form-label">Devisdate</label>
            <input type="text" id="devisDate" name="devisDate" class="form-control" value="{{ old('devisDate', $item?->devisDate) }}">
            @error('devisDate')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="devisMontant" name="devisMontant" value="{{ old('devisMontant', $item?->devisMontant) }}">


        <div class="mb-3">
            <label for="devisAcompte" class="form-label">Devisacompte</label>
            <input type="text" id="devisAcompte" name="devisAcompte" class="form-control" value="{{ old('devisAcompte', $item?->devisAcompte) }}">
            @error('devisAcompte')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisNumero" class="form-label">Devisnumero</label>
            <input type="text" id="devisNumero" name="devisNumero" class="form-control" value="{{ old('devisNumero', $item?->devisNumero) }}">
            @error('devisNumero')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisLieu" class="form-label">Devislieu</label>
            <input type="text" id="devisLieu" name="devisLieu" class="form-control" value="{{ old('devisLieu', $item?->devisLieu) }}">
            @error('devisLieu')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisOrga" class="form-label">Devisorga</label>
            <input type="text" id="devisOrga" name="devisOrga" class="form-control" value="{{ old('devisOrga', $item?->devisOrga) }}">
            @error('devisOrga')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisCivilite" class="form-label">Deviscivilite</label>
            <input type="text" id="devisCivilite" name="devisCivilite" class="form-control" value="{{ old('devisCivilite', $item?->devisCivilite) }}">
            @error('devisCivilite')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisContact" class="form-label">Deviscontact</label>
            <input type="text" id="devisContact" name="devisContact" class="form-control" value="{{ old('devisContact', $item?->devisContact) }}">
            @error('devisContact')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisCP" class="form-label">Deviscp</label>
            <input type="text" id="devisCP" name="devisCP" class="form-control" value="{{ old('devisCP', $item?->devisCP) }}">
            @error('devisCP')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisVille" class="form-label">Devisville</label>
            <input type="text" id="devisVille" name="devisVille" class="form-control" value="{{ old('devisVille', $item?->devisVille) }}">
            @error('devisVille')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisTel1" class="form-label">Devistel1</label>
            <input type="text" id="devisTel1" name="devisTel1" class="form-control" value="{{ old('devisTel1', $item?->devisTel1) }}">
            @error('devisTel1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisTel2" class="form-label">Devistel2</label>
            <input type="text" id="devisTel2" name="devisTel2" class="form-control" value="{{ old('devisTel2', $item?->devisTel2) }}">
            @error('devisTel2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisFax" class="form-label">Devisfax</label>
            <input type="text" id="devisFax" name="devisFax" class="form-control" value="{{ old('devisFax', $item?->devisFax) }}">
            @error('devisFax')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisEmail" class="form-label">Devisemail</label>
            <input type="text" id="devisEmail" name="devisEmail" class="form-control" value="{{ old('devisEmail', $item?->devisEmail) }}">
            @error('devisEmail')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="devisURL" class="form-label">Devisurl</label>
            <input type="text" id="devisURL" name="devisURL" class="form-control" value="{{ old('devisURL', $item?->devisURL) }}">
            @error('devisURL')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="factDate" class="form-label">Factdate</label>
            <input type="text" id="factDate" name="factDate" class="form-control" value="{{ old('factDate', $item?->factDate) }}">
            @error('factDate')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="factMontant" name="factMontant" value="{{ old('factMontant', $item?->factMontant) }}">


        <div class="mb-3">
            <label for="CopieDevis" class="form-label">Copiedevis</label>
            <input type="text" id="CopieDevis" name="CopieDevis" class="form-control" value="{{ old('CopieDevis', $item?->CopieDevis) }}">
            @error('CopieDevis')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="factAcompte" class="form-label">Factacompte</label>
            <input type="text" id="factAcompte" name="factAcompte" class="form-control" value="{{ old('factAcompte', $item?->factAcompte) }}">
            @error('factAcompte')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_facturation.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
