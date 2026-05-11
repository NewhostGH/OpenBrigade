@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: astreinte_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">AstreinteEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.astreinte_edit.update', $itemKey) : route('legacy_migrated.astreinte_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="astreinte" name="astreinte" value="{{ old('astreinte', $item?->astreinte) }}">

<input type="hidden" id="section" name="section" value="{{ old('section', $item?->section) }}">

<input type="hidden" id="type" name="type" value="{{ old('type', $item?->type) }}">


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


        <div class="mb-3">
            <label for="person" class="form-label">Person</label>
            <textarea id="person" name="person" class="form-control" rows="4">{{ old('person', $item?->person) }}</textarea>
            @error('person')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.astreinte_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
