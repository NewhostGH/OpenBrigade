@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_poste.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Poste Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_poste.update', $itemKey) : route('legacy_migrated.upd_poste.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="PS_ID" name="PS_ID" value="{{ old('PS_ID', $item?->PS_ID) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="TYPE" name="TYPE" value="{{ old('TYPE', $item?->TYPE) }}">

<input type="hidden" id="DESCRIPTION" name="DESCRIPTION" value="{{ old('DESCRIPTION', $item?->DESCRIPTION) }}">

<input type="hidden" id="PS_EXPIRABLE" name="PS_EXPIRABLE" value="{{ old('PS_EXPIRABLE', $item?->PS_EXPIRABLE) }}">

<input type="hidden" id="PS_AUDIT" name="PS_AUDIT" value="{{ old('PS_AUDIT', $item?->PS_AUDIT) }}">

<input type="hidden" id="PS_DIPLOMA" name="PS_DIPLOMA" value="{{ old('PS_DIPLOMA', $item?->PS_DIPLOMA) }}">

<input type="hidden" id="PS_NUMERO" name="PS_NUMERO" value="{{ old('PS_NUMERO', $item?->PS_NUMERO) }}">

<input type="hidden" id="PS_SECOURISME" name="PS_SECOURISME" value="{{ old('PS_SECOURISME', $item?->PS_SECOURISME) }}">

<input type="hidden" id="PS_NATIONAL" name="PS_NATIONAL" value="{{ old('PS_NATIONAL', $item?->PS_NATIONAL) }}">

<input type="hidden" id="PS_PRINTABLE" name="PS_PRINTABLE" value="{{ old('PS_PRINTABLE', $item?->PS_PRINTABLE) }}">

<input type="hidden" id="PS_PRINT_IMAGE" name="PS_PRINT_IMAGE" value="{{ old('PS_PRINT_IMAGE', $item?->PS_PRINT_IMAGE) }}">

<input type="hidden" id="PS_FORMATION" name="PS_FORMATION" value="{{ old('PS_FORMATION', $item?->PS_FORMATION) }}">

<input type="hidden" id="PS_RECYCLE" name="PS_RECYCLE" value="{{ old('PS_RECYCLE', $item?->PS_RECYCLE) }}">

<input type="hidden" id="PS_USER_MODIFIABLE" name="PS_USER_MODIFIABLE" value="{{ old('PS_USER_MODIFIABLE', $item?->PS_USER_MODIFIABLE) }}">

<input type="hidden" id="F_ID" name="F_ID" value="{{ old('F_ID', $item?->F_ID) }}">

<input type="hidden" id="PH_CODE" name="PH_CODE" value="{{ old('PH_CODE', $item?->PH_CODE) }}">

<input type="hidden" id="PH_LEVEL" name="PH_LEVEL" value="{{ old('PH_LEVEL', $item?->PH_LEVEL) }}">

<input type="hidden" id="DAYS_WARNING" name="DAYS_WARNING" value="{{ old('DAYS_WARNING', $item?->DAYS_WARNING) }}">


        <div class="mb-3">
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_poste.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
