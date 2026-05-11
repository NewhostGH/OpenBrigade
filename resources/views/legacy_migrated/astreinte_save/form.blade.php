@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: astreinte_save.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Astreinte Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.astreinte_save.update', $itemKey) : route('legacy_migrated.astreinte_save.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <input type="text" id="section" name="section" class="form-control" value="{{ old('section', $item?->section) }}">
            @error('section')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="person" class="form-label">Person</label>
            <input type="text" id="person" name="person" class="form-control" value="{{ old('person', $item?->person) }}">
            @error('person')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" id="type" name="type" class="form-control" value="{{ old('type', $item?->type) }}">
            @error('type')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


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
            <label for="astreinte" class="form-label">Astreinte</label>
            <input type="text" id="astreinte" name="astreinte" class="form-control" value="{{ old('astreinte', $item?->astreinte) }}">
            @error('astreinte')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.astreinte_save.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
