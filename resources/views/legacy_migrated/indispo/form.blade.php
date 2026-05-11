@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: indispo.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Indispo Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.indispo.update', $itemKey) : route('legacy_migrated.indispo.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="dc1" class="form-label">Dc1</label>
            <input type="text" id="dc1" name="dc1" class="form-control" value="{{ old('dc1', $item?->dc1) }}">
            @error('dc1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="dc2" class="form-label">Dc2</label>
            <input type="text" id="dc2" name="dc2" class="form-control" value="{{ old('dc2', $item?->dc2) }}">
            @error('dc2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="duree" name="duree" value="{{ old('duree', $item?->duree) }}">


        <div class="mb-3">
            <label for="full_day" class="form-label">Full Day</label>
            <input type="text" id="full_day" name="full_day" class="form-control" value="{{ old('full_day', $item?->full_day) }}">
            @error('full_day')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="morning" class="form-label">Morning</label>
            <input type="text" id="morning" name="morning" class="form-control" value="{{ old('morning', $item?->morning) }}">
            @error('morning')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="afternoon" class="form-label">Afternoon</label>
            <input type="text" id="afternoon" name="afternoon" class="form-control" value="{{ old('afternoon', $item?->afternoon) }}">
            @error('afternoon')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="comment" class="form-label">Comment</label>
            <input type="text" id="comment" name="comment" class="form-control" value="{{ old('comment', $item?->comment) }}">
            @error('comment')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="s1" class="form-label">S1</label>
            <textarea id="s1" name="s1" class="form-control" rows="4">{{ old('s1', $item?->s1) }}</textarea>
            @error('s1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="person" class="form-label">Person</label>
            <textarea id="person" name="person" class="form-control" rows="4">{{ old('person', $item?->person) }}</textarea>
            @error('person')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <textarea id="type" name="type" class="form-control" rows="4">{{ old('type', $item?->type) }}</textarea>
            @error('type')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="debut" class="form-label">Debut</label>
            <textarea id="debut" name="debut" class="form-control" rows="4">{{ old('debut', $item?->debut) }}</textarea>
            @error('debut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fin" class="form-label">Fin</label>
            <textarea id="fin" name="fin" class="form-control" rows="4">{{ old('fin', $item?->fin) }}</textarea>
            @error('fin')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.indispo.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
