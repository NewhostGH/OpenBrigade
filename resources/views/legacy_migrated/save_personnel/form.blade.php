@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_personnel.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Personnel Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_personnel.update', $itemKey) : route('legacy_migrated.save_personnel.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="ignore_duplicate" name="ignore_duplicate" value="{{ old('ignore_duplicate', $item?->ignore_duplicate) }}">


        <div class="mb-3">
            <label for="P_ID" class="form-label">P Id</label>
            <input type="text" id="P_ID" name="P_ID" class="form-control" value="{{ old('P_ID', $item?->P_ID) }}">
            @error('P_ID')
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
            <label for="security" class="form-label">Security</label>
            <input type="text" id="security" name="security" class="form-control" value="{{ old('security', $item?->security) }}">
            @error('security')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="doc" class="form-label">Doc</label>
            <input type="text" id="doc" name="doc" class="form-control" value="{{ old('doc', $item?->doc) }}">
            @error('doc')
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
            <label for="suspendu" class="form-label">Suspendu</label>
            <input type="text" id="suspendu" name="suspendu" class="form-control" value="{{ old('suspendu', $item?->suspendu) }}">
            @error('suspendu')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_suspendu" class="form-label">Date Suspendu</label>
            <input type="text" id="date_suspendu" name="date_suspendu" class="form-control" value="{{ old('date_suspendu', $item?->date_suspendu) }}">
            @error('date_suspendu')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_fin_suspendu" class="form-label">Date Fin Suspendu</label>
            <input type="text" id="date_fin_suspendu" name="date_fin_suspendu" class="form-control" value="{{ old('date_fin_suspendu', $item?->date_fin_suspendu) }}">
            @error('date_fin_suspendu')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="npai" class="form-label">Npai</label>
            <input type="text" id="npai" name="npai" class="form-control" value="{{ old('npai', $item?->npai) }}">
            @error('npai')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_npai" class="form-label">Date Npai</label>
            <input type="text" id="date_npai" name="date_npai" class="form-control" value="{{ old('date_npai', $item?->date_npai) }}">
            @error('date_npai')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="debut" class="form-label">Debut</label>
            <input type="text" id="debut" name="debut" class="form-control" value="{{ old('debut', $item?->debut) }}">
            @error('debut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fin" class="form-label">Fin</label>
            <input type="text" id="fin" name="fin" class="form-control" value="{{ old('fin', $item?->fin) }}">
            @error('fin')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="licnum" class="form-label">Licnum</label>
            <input type="text" id="licnum" name="licnum" class="form-control" value="{{ old('licnum', $item?->licnum) }}">
            @error('licnum')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="licence_date" class="form-label">Licence Date</label>
            <input type="text" id="licence_date" name="licence_date" class="form-control" value="{{ old('licence_date', $item?->licence_date) }}">
            @error('licence_date')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="licence_end" class="form-label">Licence End</label>
            <input type="text" id="licence_end" name="licence_end" class="form-control" value="{{ old('licence_end', $item?->licence_end) }}">
            @error('licence_end')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="id_api" class="form-label">Id Api</label>
            <input type="text" id="id_api" name="id_api" class="form-control" value="{{ old('id_api', $item?->id_api) }}">
            @error('id_api')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="birth" class="form-label">Birth</label>
            <input type="text" id="birth" name="birth" class="form-control" value="{{ old('birth', $item?->birth) }}">
            @error('birth')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="service" class="form-label">Service</label>
            <input type="text" id="service" name="service" class="form-control" value="{{ old('service', $item?->service) }}">
            @error('service')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="motif_radiation" class="form-label">Motif Radiation</label>
            <input type="text" id="motif_radiation" name="motif_radiation" class="form-control" value="{{ old('motif_radiation', $item?->motif_radiation) }}">
            @error('motif_radiation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_personnel.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
