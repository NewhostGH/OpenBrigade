@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: search_personnel_result.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">SearchPersonnelResult Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.search_personnel_result.update', $itemKey) : route('legacy_migrated.search_personnel_result.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="SelectionMail" name="SelectionMail" value="{{ old('SelectionMail', $item?->SelectionMail) }}">


        <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <input type="text" id="section" name="section" class="form-control" value="{{ old('section', $item?->section) }}">
            @error('section')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="typetri" class="form-label">Typetri</label>
            <input type="text" id="typetri" name="typetri" class="form-control" value="{{ old('typetri', $item?->typetri) }}">
            @error('typetri')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="statut" class="form-label">Statut</label>
            <input type="text" id="statut" name="statut" class="form-control" value="{{ old('statut', $item?->statut) }}">
            @error('statut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="trouve" class="form-label">Trouve</label>
            <input type="text" id="trouve" name="trouve" class="form-control" value="{{ old('trouve', $item?->trouve) }}">
            @error('trouve')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="qualif" class="form-label">Qualif</label>
            <input type="text" id="qualif" name="qualif" class="form-control" value="{{ old('qualif', $item?->qualif) }}">
            @error('qualif')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.search_personnel_result.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
