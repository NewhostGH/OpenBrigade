@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: homonymes_manage.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">HomonymesManage Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.homonymes_manage.update', $itemKey) : route('legacy_migrated.homonymes_manage.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="pid" name="pid" value="{{ old('pid', $item?->pid) }}">

<input type="hidden" id="doublon_id" name="doublon_id" value="{{ old('doublon_id', $item?->doublon_id) }}">


        <div class="mb-3">
            <label for="competences" class="form-label">Competences</label>
            <input type="text" id="competences" name="competences" class="form-control" value="{{ old('competences', $item?->competences) }}">
            @error('competences')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="formations" class="form-label">Formations</label>
            <input type="text" id="formations" name="formations" class="form-control" value="{{ old('formations', $item?->formations) }}">
            @error('formations')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="participations" class="form-label">Participations</label>
            <input type="text" id="participations" name="participations" class="form-control" value="{{ old('participations', $item?->participations) }}">
            @error('participations')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="radier" class="form-label">Radier</label>
            <input type="text" id="radier" name="radier" class="form-control" value="{{ old('radier', $item?->radier) }}">
            @error('radier')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="supprimer" class="form-label">Supprimer</label>
            <input type="text" id="supprimer" name="supprimer" class="form-control" value="{{ old('supprimer', $item?->supprimer) }}">
            @error('supprimer')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.homonymes_manage.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
