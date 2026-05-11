@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_multi_renforts.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementMultiRenforts Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_multi_renforts.update', $itemKey) : route('legacy_migrated.evenement_multi_renforts.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="yesall" class="form-label">Yesall</label>
            <input type="text" id="yesall" name="yesall" class="form-control" value="{{ old('yesall', $item?->yesall) }}">
            @error('yesall')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="noall" class="form-label">Noall</label>
            <input type="text" id="noall" name="noall" class="form-control" value="{{ old('noall', $item?->noall) }}">
            @error('noall')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="confirmed" name="confirmed" value="{{ old('confirmed', $item?->confirmed) }}">


        <div class="mb-3">
            <label for="check_" class="form-label">Check </label>
            <input type="text" id="check_" name="check_" class="form-control" value="{{ old('check_', $item?->check_) }}">
            @error('check_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_multi_renforts.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
