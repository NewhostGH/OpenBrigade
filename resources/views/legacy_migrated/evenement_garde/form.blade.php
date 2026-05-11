@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_garde.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementGarde Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_garde.update', $itemKey) : route('legacy_migrated.evenement_garde.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="show_spp" class="form-label">Show Spp</label>
            <input type="text" id="show_spp" name="show_spp" class="form-control" value="{{ old('show_spp', $item?->show_spp) }}">
            @error('show_spp')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="show_indispos" class="form-label">Show Indispos</label>
            <input type="text" id="show_indispos" name="show_indispos" class="form-control" value="{{ old('show_indispos', $item?->show_indispos) }}">
            @error('show_indispos')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="total2" name="total2" value="{{ old('total2', $item?->total2) }}">


        <div class="mb-3">
            <label for="display_order" class="form-label">Display Order</label>
            <textarea id="display_order" name="display_order" class="form-control" rows="4">{{ old('display_order', $item?->display_order) }}</textarea>
            @error('display_order')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_garde.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
