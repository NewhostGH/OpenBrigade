@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_accueil.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Accueil Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_accueil.update', $itemKey) : route('legacy_migrated.save_accueil.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="pid" class="form-label">Pid</label>
            <input type="text" id="pid" name="pid" class="form-control" value="{{ old('pid', $item?->pid) }}">
            @error('pid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="wid" class="form-label">Wid</label>
            <input type="text" id="wid" name="wid" class="form-control" value="{{ old('wid', $item?->wid) }}">
            @error('wid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="zone" class="form-label">Zone</label>
            <input type="text" id="zone" name="zone" class="form-control" value="{{ old('zone', $item?->zone) }}">
            @error('zone')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="position" class="form-label">Position</label>
            <input type="text" id="position" name="position" class="form-control" value="{{ old('position', $item?->position) }}">
            @error('position')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="show" class="form-label">Show</label>
            <input type="text" id="show" name="show" class="form-control" value="{{ old('show', $item?->show) }}">
            @error('show')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_accueil.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
