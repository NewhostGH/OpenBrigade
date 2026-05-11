@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: personnel_preferences.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">PersonnelPreferences Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.personnel_preferences.update', $itemKey) : route('legacy_migrated.personnel_preferences.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="$chkname" class="form-label">$Chkname</label>
            <input type="text" id="$chkname" name="$chkname" class="form-control" value="{{ old('$chkname', $item?->$chkname) }}">
            @error('$chkname')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="pid" name="pid" value="{{ old('pid', $item?->pid) }}">

<input type="hidden" id="f$ID" name="f$ID" value="{{ old('f$ID', $item?->f$ID) }}">


        <div class="mb-3">
            <label for="switchstats" class="form-label">Switchstats</label>
            <input type="text" id="switchstats" name="switchstats" class="form-control" value="{{ old('switchstats', $item?->switchstats) }}">
            @error('switchstats')
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

<input type="hidden" id="U" name="U" value="{{ old('U', $item?->U) }}">


        <div class="mb-3">
            <label for="F" class="form-label">F</label>
            <input type="text" id="F" name="F" class="form-control" value="{{ old('F', $item?->F) }}">
            @error('F')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="prefCalend" class="form-label">Prefcalend</label>
            <textarea id="prefCalend" name="prefCalend" class="form-control" rows="4">{{ old('prefCalend', $item?->prefCalend) }}</textarea>
            @error('prefCalend')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.personnel_preferences.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
