@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: personnel_contact.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">PersonnelContact Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.personnel_contact.update', $itemKey) : route('legacy_migrated.personnel_contact.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="person" name="person" value="{{ old('person', $item?->person) }}">


        <div class="mb-3">
            <label for="c" class="form-label">C</label>
            <input type="text" id="c" name="c" class="form-control" value="{{ old('c', $item?->c) }}">
            @error('c')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.personnel_contact.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
