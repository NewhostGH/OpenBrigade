@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_cotisations.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Cotisations Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_cotisations.update', $itemKey) : route('legacy_migrated.save_cotisations.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="filter" class="form-label">Filter</label>
            <input type="text" id="filter" name="filter" class="form-control" value="{{ old('filter', $item?->filter) }}">
            @error('filter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="year" class="form-label">Year</label>
            <input type="text" id="year" name="year" class="form-control" value="{{ old('year', $item?->year) }}">
            @error('year')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="periode" class="form-label">Periode</label>
            <input type="text" id="periode" name="periode" class="form-control" value="{{ old('periode', $item?->periode) }}">
            @error('periode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type_paiement" class="form-label">Type Paiement</label>
            <input type="text" id="type_paiement" name="type_paiement" class="form-control" value="{{ old('type_paiement', $item?->type_paiement) }}">
            @error('type_paiement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="people" class="form-label">People</label>
            <input type="text" id="people" name="people" class="form-control" value="{{ old('people', $item?->people) }}">
            @error('people')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_cotisations.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
