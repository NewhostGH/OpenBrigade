@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_grades.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Grades Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_grades.update', $itemKey) : route('legacy_migrated.upd_grades.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="OLD_G_GRADE" name="OLD_G_GRADE" value="{{ old('OLD_G_GRADE', $item?->OLD_G_GRADE) }}">

<input type="hidden" id="usage" name="usage" value="{{ old('usage', $item?->usage) }}">

<input type="hidden" id="categorie" name="categorie" value="{{ old('categorie', $item?->categorie) }}">


        <div class="mb-3">
            <label for="G_GRADE" class="form-label">G Grade</label>
            <input type="text" id="G_GRADE" name="G_GRADE" class="form-control" value="{{ old('G_GRADE', $item?->G_GRADE) }}">
            @error('G_GRADE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="G_DESCRIPTION" class="form-label">G Description</label>
            <input type="text" id="G_DESCRIPTION" name="G_DESCRIPTION" class="form-control" value="{{ old('G_DESCRIPTION', $item?->G_DESCRIPTION) }}">
            @error('G_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="G_LEVEL" class="form-label">G Level</label>
            <input type="text" id="G_LEVEL" name="G_LEVEL" class="form-control" value="{{ old('G_LEVEL', $item?->G_LEVEL) }}">
            @error('G_LEVEL')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="G_TYPE" class="form-label">G Type</label>
            <input type="text" id="G_TYPE" name="G_TYPE" class="form-control" value="{{ old('G_TYPE', $item?->G_TYPE) }}">
            @error('G_TYPE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="icone" class="form-label">Icone</label>
            <input type="text" id="icone" name="icone" class="form-control" value="{{ old('icone', $item?->icone) }}">
            @error('icone')
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_grades.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
