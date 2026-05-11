@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_edit.update', $itemKey) : route('legacy_migrated.evenement_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="mail1" name="mail1" value="{{ old('mail1', $item?->mail1) }}">

<input type="hidden" id="mail2" name="mail2" value="{{ old('mail2', $item?->mail2) }}">

<input type="hidden" id="mail3" name="mail3" value="{{ old('mail3', $item?->mail3) }}">

<input type="hidden" id="parent" name="parent" value="{{ old('parent', $item?->parent) }}">

<input type="hidden" id="allow_reinforcement" name="allow_reinforcement" value="{{ old('allow_reinforcement', $item?->allow_reinforcement) }}">

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">

<input type="hidden" id="copydetails" name="copydetails" value="{{ old('copydetails', $item?->copydetails) }}">

<input type="hidden" id="agreed" name="agreed" value="{{ old('agreed', $item?->agreed) }}">


        <div class="mb-3">
            <label for="renforts" class="form-label">Renforts</label>
            <input type="text" id="renforts" name="renforts" class="form-control" value="{{ old('renforts', $item?->renforts) }}">
            @error('renforts')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="vehicules" class="form-label">Vehicules</label>
            <input type="text" id="vehicules" name="vehicules" class="form-control" value="{{ old('vehicules', $item?->vehicules) }}">
            @error('vehicules')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="materiel" class="form-label">Materiel</label>
            <input type="text" id="materiel" name="materiel" class="form-control" value="{{ old('materiel', $item?->materiel) }}">
            @error('materiel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="personnel" class="form-label">Personnel</label>
            <input type="text" id="personnel" name="personnel" class="form-control" value="{{ old('personnel', $item?->personnel) }}">
            @error('personnel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="options" class="form-label">Options</label>
            <input type="text" id="options" name="options" class="form-control" value="{{ old('options', $item?->options) }}">
            @error('options')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="copydetailsfrom" name="copydetailsfrom" value="{{ old('copydetailsfrom', $item?->copydetailsfrom) }}">

<input type="hidden" id="copycheffrom" name="copycheffrom" value="{{ old('copycheffrom', $item?->copycheffrom) }}">

<input type="hidden" id="type" name="type" value="{{ old('type', $item?->type) }}">

<input type="hidden" id="type_garde" name="type_garde" value="{{ old('type_garde', $item?->type_garde) }}">


        <div class="mb-3">
            <label for="libelle" class="form-label">Libelle</label>
            <input type="text" id="libelle" name="libelle" class="form-control" value="{{ old('libelle', $item?->libelle) }}">
            @error('libelle')
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
