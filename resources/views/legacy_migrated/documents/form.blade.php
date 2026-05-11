@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: documents.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Documents Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.documents.update', $itemKey) : route('legacy_migrated.documents.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="section" name="section" value="{{ old('section', $item?->section) }}">


        <div class="mb-3">
            <label for="goup" class="form-label">Goup</label>
            <input type="text" id="goup" name="goup" class="form-control" value="{{ old('goup', $item?->goup) }}">
            @error('goup')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="yeardoc" class="form-label">Yeardoc</label>
            <textarea id="yeardoc" name="yeardoc" class="form-control" rows="4">{{ old('yeardoc', $item?->yeardoc) }}</textarea>
            @error('yeardoc')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="td" class="form-label">Td</label>
            <textarea id="td" name="td" class="form-control" rows="4">{{ old('td', $item?->td) }}</textarea>
            @error('td')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.documents.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
