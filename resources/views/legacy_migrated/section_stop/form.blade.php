@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: section_stop.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">SectionStop Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.section_stop.update', $itemKey) : route('legacy_migrated.section_stop.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="section" name="section" value="{{ old('section', $item?->section) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">

<input type="hidden" id="sseid" name="sseid" value="{{ old('sseid', $item?->sseid) }}">


        <div class="mb-3">
            <label for="start" class="form-label">Start</label>
            <input type="text" id="start" name="start" class="form-control" value="{{ old('start', $item?->start) }}">
            @error('start')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="end" class="form-label">End</label>
            <input type="text" id="end" name="end" class="form-control" value="{{ old('end', $item?->end) }}">
            @error('end')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="active" class="form-label">Active</label>
            <input type="text" id="active" name="active" class="form-control" value="{{ old('active', $item?->active) }}">
            @error('active')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="comment" class="form-label">Comment</label>
            <textarea id="comment" name="comment" class="form-control" rows="4">{{ old('comment', $item?->comment) }}</textarea>
            @error('comment')
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.section_stop.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
