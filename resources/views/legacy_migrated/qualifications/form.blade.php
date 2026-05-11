@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: qualifications.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Qualifications Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.qualifications.update', $itemKey) : route('legacy_migrated.qualifications.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="typequalif" name="typequalif" value="{{ old('typequalif', $item?->typequalif) }}">

<input type="hidden" id="pompier" name="pompier" value="{{ old('pompier', $item?->pompier) }}">

<input type="hidden" id="order" name="order" value="{{ old('order', $item?->order) }}">

<input type="hidden" id="filter" name="filter" value="{{ old('filter', $item?->filter) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">


        <div class="mb-3">
            <label for="sub" class="form-label">Sub</label>
            <input type="text" id="sub" name="sub" class="form-control" value="{{ old('sub', $item?->sub) }}">
            @error('sub')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="competence" name="competence" value="{{ old('competence', $item?->competence) }}">


        <div class="mb-3">
            <label for="$P_ID" class="form-label">$P Id</label>
            <input type="text" id="$P_ID" name="$P_ID" class="form-control" value="{{ old('$P_ID', $item?->$P_ID) }}">
            @error('$P_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="exp_" name="exp_" value="{{ old('exp_', $item?->exp_) }}">

<input type="hidden" id="updated_" name="updated_" value="{{ old('updated_', $item?->updated_) }}">


        <div class="mb-3">
            <label for="$PS_ID" class="form-label">$Ps Id</label>
            <input type="text" id="$PS_ID" name="$PS_ID" class="form-control" value="{{ old('$PS_ID', $item?->$PS_ID) }}">
            @error('$PS_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="Retour" class="form-label">Retour</label>
            <input type="text" id="Retour" name="Retour" class="form-control" value="{{ old('Retour', $item?->Retour) }}">
            @error('Retour')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="filter_one" class="form-label">Filter One</label>
            <textarea id="filter_one" name="filter_one" class="form-control" rows="4">{{ old('filter_one', $item?->filter_one) }}</textarea>
            @error('filter_one')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.qualifications.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
