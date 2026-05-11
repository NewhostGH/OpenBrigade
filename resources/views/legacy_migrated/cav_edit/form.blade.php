@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: cav_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">CavEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.cav_edit.update', $itemKey) : route('legacy_migrated.cav_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


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
            <label for="numcav" class="form-label">Numcav</label>
            <input type="text" id="numcav" name="numcav" class="form-control" value="{{ old('numcav', $item?->numcav) }}">
            @error('numcav')
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
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $item?->name) }}">
            @error('name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="ouvert" class="form-label">Ouvert</label>
            <input type="text" id="ouvert" name="ouvert" class="form-control" value="{{ old('ouvert', $item?->ouvert) }}">
            @error('ouvert')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.cav_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
