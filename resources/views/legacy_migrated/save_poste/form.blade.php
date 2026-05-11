@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_poste.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Poste Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_poste.update', $itemKey) : route('legacy_migrated.save_poste.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="PS_ID" class="form-label">Ps Id</label>
            <input type="text" id="PS_ID" name="PS_ID" class="form-control" value="{{ old('PS_ID', $item?->PS_ID) }}">
            @error('PS_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_ORDER" class="form-label">Ps Order</label>
            <input type="text" id="PS_ORDER" name="PS_ORDER" class="form-control" value="{{ old('PS_ORDER', $item?->PS_ORDER) }}">
            @error('PS_ORDER')
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
            <label for="TYPE" class="form-label">Type</label>
            <input type="text" id="TYPE" name="TYPE" class="form-control" value="{{ old('TYPE', $item?->TYPE) }}">
            @error('TYPE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="DESCRIPTION" class="form-label">Description</label>
            <input type="text" id="DESCRIPTION" name="DESCRIPTION" class="form-control" value="{{ old('DESCRIPTION', $item?->DESCRIPTION) }}">
            @error('DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_EXPIRABLE" class="form-label">Ps Expirable</label>
            <input type="text" id="PS_EXPIRABLE" name="PS_EXPIRABLE" class="form-control" value="{{ old('PS_EXPIRABLE', $item?->PS_EXPIRABLE) }}">
            @error('PS_EXPIRABLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_AUDIT" class="form-label">Ps Audit</label>
            <input type="text" id="PS_AUDIT" name="PS_AUDIT" class="form-control" value="{{ old('PS_AUDIT', $item?->PS_AUDIT) }}">
            @error('PS_AUDIT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_DIPLOMA" class="form-label">Ps Diploma</label>
            <input type="text" id="PS_DIPLOMA" name="PS_DIPLOMA" class="form-control" value="{{ old('PS_DIPLOMA', $item?->PS_DIPLOMA) }}">
            @error('PS_DIPLOMA')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_NUMERO" class="form-label">Ps Numero</label>
            <input type="text" id="PS_NUMERO" name="PS_NUMERO" class="form-control" value="{{ old('PS_NUMERO', $item?->PS_NUMERO) }}">
            @error('PS_NUMERO')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_FORMATION" class="form-label">Ps Formation</label>
            <input type="text" id="PS_FORMATION" name="PS_FORMATION" class="form-control" value="{{ old('PS_FORMATION', $item?->PS_FORMATION) }}">
            @error('PS_FORMATION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_SECOURISME" class="form-label">Ps Secourisme</label>
            <input type="text" id="PS_SECOURISME" name="PS_SECOURISME" class="form-control" value="{{ old('PS_SECOURISME', $item?->PS_SECOURISME) }}">
            @error('PS_SECOURISME')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_NATIONAL" class="form-label">Ps National</label>
            <input type="text" id="PS_NATIONAL" name="PS_NATIONAL" class="form-control" value="{{ old('PS_NATIONAL', $item?->PS_NATIONAL) }}">
            @error('PS_NATIONAL')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_PRINTABLE" class="form-label">Ps Printable</label>
            <input type="text" id="PS_PRINTABLE" name="PS_PRINTABLE" class="form-control" value="{{ old('PS_PRINTABLE', $item?->PS_PRINTABLE) }}">
            @error('PS_PRINTABLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_PRINT_IMAGE" class="form-label">Ps Print Image</label>
            <input type="text" id="PS_PRINT_IMAGE" name="PS_PRINT_IMAGE" class="form-control" value="{{ old('PS_PRINT_IMAGE', $item?->PS_PRINT_IMAGE) }}">
            @error('PS_PRINT_IMAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_RECYCLE" class="form-label">Ps Recycle</label>
            <input type="text" id="PS_RECYCLE" name="PS_RECYCLE" class="form-control" value="{{ old('PS_RECYCLE', $item?->PS_RECYCLE) }}">
            @error('PS_RECYCLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_USER_MODIFIABLE" class="form-label">Ps User Modifiable</label>
            <input type="text" id="PS_USER_MODIFIABLE" name="PS_USER_MODIFIABLE" class="form-control" value="{{ old('PS_USER_MODIFIABLE', $item?->PS_USER_MODIFIABLE) }}">
            @error('PS_USER_MODIFIABLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_ID" class="form-label">Eq Id</label>
            <input type="text" id="EQ_ID" name="EQ_ID" class="form-control" value="{{ old('EQ_ID', $item?->EQ_ID) }}">
            @error('EQ_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="F_ID" class="form-label">F Id</label>
            <input type="text" id="F_ID" name="F_ID" class="form-control" value="{{ old('F_ID', $item?->F_ID) }}">
            @error('F_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PH_CODE" class="form-label">Ph Code</label>
            <input type="text" id="PH_CODE" name="PH_CODE" class="form-control" value="{{ old('PH_CODE', $item?->PH_CODE) }}">
            @error('PH_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="DAYS_WARNING" class="form-label">Days Warning</label>
            <input type="text" id="DAYS_WARNING" name="DAYS_WARNING" class="form-control" value="{{ old('DAYS_WARNING', $item?->DAYS_WARNING) }}">
            @error('DAYS_WARNING')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_poste.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
