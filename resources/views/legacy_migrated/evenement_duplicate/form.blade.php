@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_duplicate.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementDuplicate Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_duplicate.update', $itemKey) : route('legacy_migrated.evenement_duplicate.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">


        <div class="mb-3">
            <label for="D" class="form-label">D</label>
            <input type="text" id="D" name="D" class="form-control" value="{{ old('D', $item?->D) }}">
            @error('D')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="P" class="form-label">P</label>
            <input type="text" id="P" name="P" class="form-control" value="{{ old('P', $item?->P) }}">
            @error('P')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="V" class="form-label">V</label>
            <input type="text" id="V" name="V" class="form-control" value="{{ old('V', $item?->V) }}">
            @error('V')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="numweeks" class="form-label">Numweeks</label>
            <textarea id="numweeks" name="numweeks" class="form-control" rows="4">{{ old('numweeks', $item?->numweeks) }}</textarea>
            @error('numweeks')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_duplicate.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
