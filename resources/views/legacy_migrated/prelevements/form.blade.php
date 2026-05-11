@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: prelevements.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Prelevements Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.prelevements.update', $itemKey) : route('legacy_migrated.prelevements.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="sub" class="form-label">Sub</label>
            <input type="text" id="sub" name="sub" class="form-control" value="{{ old('sub', $item?->sub) }}">
            @error('sub')
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

<input type="hidden" id="filter" name="filter" value="{{ old('filter', $item?->filter) }}">

<input type="hidden" id="year" name="year" value="{{ old('year', $item?->year) }}">

<input type="hidden" id="periode" name="periode" value="{{ old('periode', $item?->periode) }}">

<input type="hidden" id="subsections" name="subsections" value="{{ old('subsections', $item?->subsections) }}">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.prelevements.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
