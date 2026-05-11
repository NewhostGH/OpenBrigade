@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_consommable_add.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementConsommableAdd Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_consommable_add.update', $itemKey) : route('legacy_migrated.evenement_consommable_add.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="from" class="form-label">From</label>
            <input type="text" id="from" name="from" class="form-control" value="{{ old('from', $item?->from) }}">
            @error('from')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="evenement" class="form-label">Evenement</label>
            <input type="text" id="evenement" name="evenement" class="form-control" value="{{ old('evenement', $item?->evenement) }}">
            @error('evenement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="action" class="form-label">Action</label>
            <input type="text" id="action" name="action" class="form-control" value="{{ old('action', $item?->action) }}">
            @error('action')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="C_ID" class="form-label">C Id</label>
            <input type="text" id="C_ID" name="C_ID" class="form-control" value="{{ old('C_ID', $item?->C_ID) }}">
            @error('C_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EC_ID" class="form-label">Ec Id</label>
            <input type="text" id="EC_ID" name="EC_ID" class="form-control" value="{{ old('EC_ID', $item?->EC_ID) }}">
            @error('EC_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nb" class="form-label">Nb</label>
            <input type="text" id="nb" name="nb" class="form-control" value="{{ old('nb', $item?->nb) }}">
            @error('nb')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EC" class="form-label">Ec</label>
            <input type="text" id="EC" name="EC" class="form-control" value="{{ old('EC', $item?->EC) }}">
            @error('EC')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_consommable_add.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
