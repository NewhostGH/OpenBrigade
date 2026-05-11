@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_type_vehicule.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeVehicule Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_type_vehicule.update', $itemKey) : route('legacy_migrated.save_type_vehicule.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="OLD_TV_CODE" class="form-label">Old Tv Code</label>
            <input type="text" id="OLD_TV_CODE" name="OLD_TV_CODE" class="form-control" value="{{ old('OLD_TV_CODE', $item?->OLD_TV_CODE) }}">
            @error('OLD_TV_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TV_CODE" class="form-label">Tv Code</label>
            <input type="text" id="TV_CODE" name="TV_CODE" class="form-control" value="{{ old('TV_CODE', $item?->TV_CODE) }}">
            @error('TV_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TV_NB" class="form-label">Tv Nb</label>
            <input type="text" id="TV_NB" name="TV_NB" class="form-control" value="{{ old('TV_NB', $item?->TV_NB) }}">
            @error('TV_NB')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TV_USAGE" class="form-label">Tv Usage</label>
            <input type="text" id="TV_USAGE" name="TV_USAGE" class="form-control" value="{{ old('TV_USAGE', $item?->TV_USAGE) }}">
            @error('TV_USAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TV_LIBELLE" class="form-label">Tv Libelle</label>
            <input type="text" id="TV_LIBELLE" name="TV_LIBELLE" class="form-control" value="{{ old('TV_LIBELLE', $item?->TV_LIBELLE) }}">
            @error('TV_LIBELLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="icon" class="form-label">Icon</label>
            <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $item?->icon) }}">
            @error('icon')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="operation" class="form-label">Operation</label>
            <input type="text" id="operation" name="operation" class="form-control" value="{{ old('operation', $item?->operation) }}">
            @error('operation')
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
            <label for="ROLE_$i" class="form-label">Role $I</label>
            <input type="text" id="ROLE_$i" name="ROLE_$i" class="form-control" value="{{ old('ROLE_$i', $item?->ROLE_$i) }}">
            @error('ROLE_$i')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_$i" class="form-label">Ps $I</label>
            <input type="text" id="PS_$i" name="PS_$i" class="form-control" value="{{ old('PS_$i', $item?->PS_$i) }}">
            @error('PS_$i')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_type_vehicule.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
