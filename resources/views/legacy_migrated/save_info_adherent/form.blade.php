@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_info_adherent.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">InfoAdherent Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_info_adherent.update', $itemKey) : route('legacy_migrated.save_info_adherent.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="P_ID" class="form-label">P Id</label>
            <input type="text" id="P_ID" name="P_ID" class="form-control" value="{{ old('P_ID', $item?->P_ID) }}">
            @error('P_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type_paiement" class="form-label">Type Paiement</label>
            <input type="text" id="type_paiement" name="type_paiement" class="form-control" value="{{ old('type_paiement', $item?->type_paiement) }}">
            @error('type_paiement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="montant_regul" class="form-label">Montant Regul</label>
            <input type="text" id="montant_regul" name="montant_regul" class="form-control" value="{{ old('montant_regul', $item?->montant_regul) }}">
            @error('montant_regul')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="bic" class="form-label">Bic</label>
            <input type="text" id="bic" name="bic" class="form-control" value="{{ old('bic', $item?->bic) }}">
            @error('bic')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="iban" class="form-label">Iban</label>
            <input type="text" id="iban" name="iban" class="form-control" value="{{ old('iban', $item?->iban) }}">
            @error('iban')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_info_adherent.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
