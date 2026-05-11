@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: dps_calc.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">DpsCalc Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.dps_calc.update', $itemKey) : route('legacy_migrated.dps_calc.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="P1" class="form-label">P1</label>
            <input type="text" id="P1" name="P1" class="form-control" value="{{ old('P1', $item?->P1) }}">
            @error('P1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="P2" class="form-label">P2</label>
            <input type="text" id="P2" name="P2" class="form-control" value="{{ old('P2', $item?->P2) }}">
            @error('P2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="E1" class="form-label">E1</label>
            <input type="text" id="E1" name="E1" class="form-control" value="{{ old('E1', $item?->E1) }}">
            @error('E1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="E2" class="form-label">E2</label>
            <input type="text" id="E2" name="E2" class="form-control" value="{{ old('E2', $item?->E2) }}">
            @error('E2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="dimNbISActeurs" class="form-label">Dimnbisacteurs</label>
            <input type="text" id="dimNbISActeurs" name="dimNbISActeurs" class="form-control" value="{{ old('dimNbISActeurs', $item?->dimNbISActeurs) }}">
            @error('dimNbISActeurs')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="dimNbISActeursCom" class="form-label">Dimnbisacteurscom</label>
            <input type="text" id="dimNbISActeursCom" name="dimNbISActeursCom" class="form-control" value="{{ old('dimNbISActeursCom', $item?->dimNbISActeursCom) }}">
            @error('dimNbISActeursCom')
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
            <label for="actionPrint" class="form-label">Actionprint</label>
            <input type="text" id="actionPrint" name="actionPrint" class="form-control" value="{{ old('actionPrint', $item?->actionPrint) }}">
            @error('actionPrint')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.dps_calc.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
