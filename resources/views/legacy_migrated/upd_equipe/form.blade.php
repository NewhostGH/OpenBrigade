@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_equipe.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Equipe Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_equipe.update', $itemKey) : route('legacy_migrated.upd_equipe.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="EQ_ID" name="EQ_ID" value="{{ old('EQ_ID', $item?->EQ_ID) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">


        <div class="mb-3">
            <label for="EQ_NOM" class="form-label">Eq Nom</label>
            <input type="text" id="EQ_NOM" name="EQ_NOM" class="form-control" value="{{ old('EQ_NOM', $item?->EQ_NOM) }}">
            @error('EQ_NOM')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_ORDER" class="form-label">Eq Order</label>
            <textarea id="EQ_ORDER" name="EQ_ORDER" class="form-control" rows="4">{{ old('EQ_ORDER', $item?->EQ_ORDER) }}</textarea>
            @error('EQ_ORDER')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_equipe.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
