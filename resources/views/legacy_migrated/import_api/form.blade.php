@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: import_api.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ImportApi Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.import_api.update', $itemKey) : route('legacy_migrated.import_api.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="start" class="form-label">Start</label>
            <input type="text" id="start" name="start" class="form-control" value="{{ old('start', $item?->start) }}">
            @error('start')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="pid" class="form-label">Pid</label>
            <input type="text" id="pid" name="pid" class="form-control" value="{{ old('pid', $item?->pid) }}">
            @error('pid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="importer" class="form-label">Importer</label>
            <input type="text" id="importer" name="importer" class="form-control" value="{{ old('importer', $item?->importer) }}">
            @error('importer')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="number" class="form-label">Number</label>
            <textarea id="number" name="number" class="form-control" rows="4">{{ old('number', $item?->number) }}</textarea>
            @error('number')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.import_api.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
