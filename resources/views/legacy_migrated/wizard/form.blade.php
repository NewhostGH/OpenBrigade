@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: wizard.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Wizard Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.wizard.update', $itemKey) : route('legacy_migrated.wizard.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="cisname" class="form-label">Cisname</label>
            <input type="text" id="cisname" name="cisname" class="form-control" value="{{ old('cisname', $item?->cisname) }}">
            @error('cisname')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="organisation_name" class="form-label">Organisation Name</label>
            <input type="text" id="organisation_name" name="organisation_name" class="form-control" value="{{ old('organisation_name', $item?->organisation_name) }}">
            @error('organisation_name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="cisurl" class="form-label">Cisurl</label>
            <input type="text" id="cisurl" name="cisurl" class="form-control" value="{{ old('cisurl', $item?->cisurl) }}">
            @error('cisurl')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="admin_email" class="form-label">Admin Email</label>
            <input type="text" id="admin_email" name="admin_email" class="form-control" value="{{ old('admin_email', $item?->admin_email) }}">
            @error('admin_email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="application_title" class="form-label">Application Title</label>
            <input type="text" id="application_title" name="application_title" class="form-control" value="{{ old('application_title', $item?->application_title) }}">
            @error('application_title')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type_organisation" class="form-label">Type Organisation</label>
            <textarea id="type_organisation" name="type_organisation" class="form-control" rows="4">{{ old('type_organisation', $item?->type_organisation) }}</textarea>
            @error('type_organisation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.wizard.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
