@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_personnel_salarie.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">PersonnelSalarie Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_personnel_salarie.update', $itemKey) : route('legacy_migrated.upd_personnel_salarie.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="person" name="person" value="{{ old('person', $item?->person) }}">


        <div class="mb-3">
            <label for="heures" class="form-label">Heures</label>
            <input type="text" id="heures" name="heures" class="form-control" value="{{ old('heures', $item?->heures) }}">
            @error('heures')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="heures_par_jour" class="form-label">Heures Par Jour</label>
            <input type="text" id="heures_par_jour" name="heures_par_jour" class="form-control" value="{{ old('heures_par_jour', $item?->heures_par_jour) }}">
            @error('heures_par_jour')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="heures_par_an" class="form-label">Heures Par An</label>
            <input type="text" id="heures_par_an" name="heures_par_an" class="form-control" value="{{ old('heures_par_an', $item?->heures_par_an) }}">
            @error('heures_par_an')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="heures_a_recuperer" class="form-label">Heures A Recuperer</label>
            <input type="text" id="heures_a_recuperer" name="heures_a_recuperer" class="form-control" value="{{ old('heures_a_recuperer', $item?->heures_a_recuperer) }}">
            @error('heures_a_recuperer')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="cp_par_an" class="form-label">Cp Par An</label>
            <input type="text" id="cp_par_an" name="cp_par_an" class="form-control" value="{{ old('cp_par_an', $item?->cp_par_an) }}">
            @error('cp_par_an')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="reliquat_cp" class="form-label">Reliquat Cp</label>
            <input type="text" id="reliquat_cp" name="reliquat_cp" class="form-control" value="{{ old('reliquat_cp', $item?->reliquat_cp) }}">
            @error('reliquat_cp')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="reliquat_rtt" class="form-label">Reliquat Rtt</label>
            <input type="text" id="reliquat_rtt" name="reliquat_rtt" class="form-control" value="{{ old('reliquat_rtt', $item?->reliquat_rtt) }}">
            @error('reliquat_rtt')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_personnel_salarie.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
