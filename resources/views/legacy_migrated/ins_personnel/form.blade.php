@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: ins_personnel.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Personnel Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.ins_personnel.update', $itemKey) : route('legacy_migrated.ins_personnel.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="P_ID" name="P_ID" value="{{ old('P_ID', $item?->P_ID) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="habilitation" name="habilitation" value="{{ old('habilitation', $item?->habilitation) }}">

<input type="hidden" id="habilitation2" name="habilitation2" value="{{ old('habilitation2', $item?->habilitation2) }}">

<input type="hidden" id="old_member" name="old_member" value="{{ old('old_member', $item?->old_member) }}">

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">


        <div class="mb-3">
            <label for="humainAnimal" class="form-label">Humainanimal</label>
            <input type="text" id="humainAnimal" name="humainAnimal" class="form-control" value="{{ old('humainAnimal', $item?->humainAnimal) }}">
            @error('humainAnimal')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="grade" name="grade" value="{{ old('grade', $item?->grade) }}">

<input type="hidden" id="statut" name="statut" value="{{ old('statut', $item?->statut) }}">


        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" id="nom" name="nom" class="form-control" value="{{ old('nom', $item?->nom) }}">
            @error('nom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="prenom" class="form-label">Prenom</label>
            <input type="text" id="prenom" name="prenom" class="form-control" value="{{ old('prenom', $item?->prenom) }}">
            @error('prenom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="matricule" class="form-label">Matricule</label>
            <input type="text" id="matricule" name="matricule" class="form-control" value="{{ old('matricule', $item?->matricule) }}">
            @error('matricule')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="groupe" name="groupe" value="{{ old('groupe', $item?->groupe) }}">

<input type="hidden" id="company" name="company" value="{{ old('company', $item?->company) }}">


        <div class="mb-3">
            <label for="debut" class="form-label">Debut</label>
            <input type="text" id="debut" name="debut" class="form-control" value="{{ old('debut', $item?->debut) }}">
            @error('debut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="birth" class="form-label">Birth</label>
            <input type="text" id="birth" name="birth" class="form-control" value="{{ old('birth', $item?->birth) }}">
            @error('birth')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="birthplace" class="form-label">Birthplace</label>
            <input type="text" id="birthplace" name="birthplace" class="form-control" value="{{ old('birthplace', $item?->birthplace) }}">
            @error('birthplace')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="birthdep" class="form-label">Birthdep</label>
            <input type="text" id="birthdep" name="birthdep" class="form-control" value="{{ old('birthdep', $item?->birthdep) }}">
            @error('birthdep')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" id="email" name="email" class="form-control" value="{{ old('email', $item?->email) }}">
            @error('email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $item?->phone) }}">
            @error('phone')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.ins_personnel.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
