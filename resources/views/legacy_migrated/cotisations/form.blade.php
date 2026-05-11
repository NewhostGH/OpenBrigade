@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: cotisations.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Cotisations Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.cotisations.update', $itemKey) : route('legacy_migrated.cotisations.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="sub" class="form-label">Sub</label>
            <input type="text" id="sub" name="sub" class="form-control" value="{{ old('sub', $item?->sub) }}">
            @error('sub')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="include_old" class="form-label">Include Old</label>
            <input type="text" id="include_old" name="include_old" class="form-control" value="{{ old('include_old', $item?->include_old) }}">
            @error('include_old')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="check_all_box" class="form-label">Check All Box</label>
            <input type="text" id="check_all_box" name="check_all_box" class="form-control" value="{{ old('check_all_box', $item?->check_all_box) }}">
            @error('check_all_box')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_" class="form-label">Date </label>
            <input type="text" id="date_" name="date_" class="form-control" value="{{ old('date_', $item?->date_) }}">
            @error('date_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="filter" class="form-label">Filter</label>
            <textarea id="filter" name="filter" class="form-control" rows="4">{{ old('filter', $item?->filter) }}</textarea>
            @error('filter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type_paiement" class="form-label">Type Paiement</label>
            <textarea id="type_paiement" name="type_paiement" class="form-control" rows="4">{{ old('type_paiement', $item?->type_paiement) }}</textarea>
            @error('type_paiement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="periode" class="form-label">Periode</label>
            <textarea id="periode" name="periode" class="form-control" rows="4">{{ old('periode', $item?->periode) }}</textarea>
            @error('periode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="year" class="form-label">Year</label>
            <textarea id="year" name="year" class="form-control" rows="4">{{ old('year', $item?->year) }}</textarea>
            @error('year')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="paid" class="form-label">Paid</label>
            <textarea id="paid" name="paid" class="form-control" rows="4">{{ old('paid', $item?->paid) }}</textarea>
            @error('paid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.cotisations.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
