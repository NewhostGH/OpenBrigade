@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_competences.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementCompetences Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_competences.update', $itemKey) : route('legacy_migrated.evenement_competences.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="global" class="form-label">Global</label>
            <input type="text" id="global" name="global" class="form-control" value="{{ old('global', $item?->global) }}">
            @error('global')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="new_competence" class="form-label">New Competence</label>
            <textarea id="new_competence" name="new_competence" class="form-control" rows="4">{{ old('new_competence', $item?->new_competence) }}</textarea>
            @error('new_competence')
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
            <label for="garde" class="form-label">Garde</label>
            <input type="text" id="garde" name="garde" class="form-control" value="{{ old('garde', $item?->garde) }}">
            @error('garde')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="partie" class="form-label">Partie</label>
            <input type="text" id="partie" name="partie" class="form-control" value="{{ old('partie', $item?->partie) }}">
            @error('partie')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="new_nb" class="form-label">New Nb</label>
            <input type="text" id="new_nb" name="new_nb" class="form-control" value="{{ old('new_nb', $item?->new_nb) }}">
            @error('new_nb')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_competences.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
