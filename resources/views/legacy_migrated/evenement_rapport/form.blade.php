@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_rapport.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementRapport Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_rapport.update', $itemKey) : route('legacy_migrated.evenement_rapport.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="save" name="save" value="{{ old('save', $item?->save) }}">

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">


        <div class="mb-3">
            <label for="responsable" class="form-label">Responsable</label>
            <input type="text" id="responsable" name="responsable" class="form-control" value="{{ old('responsable', $item?->responsable) }}">
            @error('responsable')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nombres" class="form-label">Nombres</label>
            <input type="text" id="nombres" name="nombres" class="form-control" value="{{ old('nombres', $item?->nombres) }}">
            @error('nombres')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="statistiques" class="form-label">Statistiques</label>
            <input type="text" id="statistiques" name="statistiques" class="form-control" value="{{ old('statistiques', $item?->statistiques) }}">
            @error('statistiques')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="show_cav" class="form-label">Show Cav</label>
            <input type="text" id="show_cav" name="show_cav" class="form-control" value="{{ old('show_cav', $item?->show_cav) }}">
            @error('show_cav')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="show_vehicules" class="form-label">Show Vehicules</label>
            <input type="text" id="show_vehicules" name="show_vehicules" class="form-control" value="{{ old('show_vehicules', $item?->show_vehicules) }}">
            @error('show_vehicules')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="show_materiel" class="form-label">Show Materiel</label>
            <input type="text" id="show_materiel" name="show_materiel" class="form-control" value="{{ old('show_materiel', $item?->show_materiel) }}">
            @error('show_materiel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="yesall" class="form-label">Yesall</label>
            <input type="text" id="yesall" name="yesall" class="form-control" value="{{ old('yesall', $item?->yesall) }}">
            @error('yesall')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="noall" class="form-label">Noall</label>
            <input type="text" id="noall" name="noall" class="form-control" value="{{ old('noall', $item?->noall) }}">
            @error('noall')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="check_" class="form-label">Check </label>
            <input type="text" id="check_" name="check_" class="form-control" value="{{ old('check_', $item?->check_) }}">
            @error('check_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_rapport.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
