@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: personnel_formation.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">PersonnelFormation Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.personnel_formation.update', $itemKey) : route('legacy_migrated.personnel_formation.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="dc" class="form-label">Dc</label>
            <input type="text" id="dc" name="dc" class="form-control" value="{{ old('dc', $item?->dc) }}">
            @error('dc')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="lieu" class="form-label">Lieu</label>
            <input type="text" id="lieu" name="lieu" class="form-control" value="{{ old('lieu', $item?->lieu) }}">
            @error('lieu')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="resp" class="form-label">Resp</label>
            <input type="text" id="resp" name="resp" class="form-control" value="{{ old('resp', $item?->resp) }}">
            @error('resp')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="numdiplome" class="form-label">Numdiplome</label>
            <input type="text" id="numdiplome" name="numdiplome" class="form-control" value="{{ old('numdiplome', $item?->numdiplome) }}">
            @error('numdiplome')
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
            <label for="tf" class="form-label">Tf</label>
            <textarea id="tf" name="tf" class="form-control" rows="4">{{ old('tf', $item?->tf) }}</textarea>
            @error('tf')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.personnel_formation.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
