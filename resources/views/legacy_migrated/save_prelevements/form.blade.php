@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_prelevements.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Prelevements Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_prelevements.update', $itemKey) : route('legacy_migrated.save_prelevements.store') }}">
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
            <label for="subsections" class="form-label">Subsections</label>
            <input type="text" id="subsections" name="subsections" class="form-control" value="{{ old('subsections', $item?->subsections) }}">
            @error('subsections')
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
            <label for="date_prelev" class="form-label">Date Prelev</label>
            <input type="text" id="date_prelev" name="date_prelev" class="form-control" value="{{ old('date_prelev', $item?->date_prelev) }}">
            @error('date_prelev')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_prelevements.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
