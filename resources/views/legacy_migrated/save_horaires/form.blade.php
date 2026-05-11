@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_horaires.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Horaires Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_horaires.update', $itemKey) : route('legacy_migrated.save_horaires.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="person" class="form-label">Person</label>
            <input type="text" id="person" name="person" class="form-control" value="{{ old('person', $item?->person) }}">
            @error('person')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="from" class="form-label">From</label>
            <input type="text" id="from" name="from" class="form-control" value="{{ old('from', $item?->from) }}">
            @error('from')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="week" class="form-label">Week</label>
            <input type="text" id="week" name="week" class="form-control" value="{{ old('week', $item?->week) }}">
            @error('week')
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
            <label for="status" class="form-label">Status</label>
            <input type="text" id="status" name="status" class="form-control" value="{{ old('status', $item?->status) }}">
            @error('status')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_horaires.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
