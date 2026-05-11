@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: install_addon.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">InstallAddon Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.install_addon.update', $itemKey) : route('legacy_migrated.install_addon.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="module" class="form-label">Module</label>
            <input type="text" id="module" name="module" class="form-control" value="{{ old('module', $item?->module) }}">
            @error('module')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <input type="text" id="reason" name="reason" class="form-control" value="{{ old('reason', $item?->reason) }}">
            @error('reason')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="version" class="form-label">Version</label>
            <input type="text" id="version" name="version" class="form-control" value="{{ old('version', $item?->version) }}">
            @error('version')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="licence" class="form-label">Licence</label>
            <input type="text" id="licence" name="licence" class="form-control" value="{{ old('licence', $item?->licence) }}">
            @error('licence')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="libelle" class="form-label">Libelle</label>
            <input type="text" id="libelle" name="libelle" class="form-control" value="{{ old('libelle', $item?->libelle) }}">
            @error('libelle')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" id="description" name="description" class="form-control" value="{{ old('description', $item?->description) }}">
            @error('description')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="end_datetime" class="form-label">End Datetime</label>
            <input type="text" id="end_datetime" name="end_datetime" class="form-control" value="{{ old('end_datetime', $item?->end_datetime) }}">
            @error('end_datetime')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="section_id" class="form-label">Section Id</label>
            <input type="text" id="section_id" name="section_id" class="form-control" value="{{ old('section_id', $item?->section_id) }}">
            @error('section_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="seats" class="form-label">Seats</label>
            <input type="text" id="seats" name="seats" class="form-control" value="{{ old('seats', $item?->seats) }}">
            @error('seats')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.install_addon.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
