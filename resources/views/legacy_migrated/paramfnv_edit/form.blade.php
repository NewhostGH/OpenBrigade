@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: paramfnv_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ParamfnvEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.paramfnv_edit.update', $itemKey) : route('legacy_migrated.paramfnv_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="TFV_ID" name="TFV_ID" value="{{ old('TFV_ID', $item?->TFV_ID) }}">


        <div class="mb-3">
            <label for="TFV_NAME" class="form-label">Tfv Name</label>
            <input type="text" id="TFV_NAME" name="TFV_NAME" class="form-control" value="{{ old('TFV_NAME', $item?->TFV_NAME) }}">
            @error('TFV_NAME')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TFV_DESCRIPTION" class="form-label">Tfv Description</label>
            <input type="text" id="TFV_DESCRIPTION" name="TFV_DESCRIPTION" class="form-control" value="{{ old('TFV_DESCRIPTION', $item?->TFV_DESCRIPTION) }}">
            @error('TFV_DESCRIPTION')
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
            <label for="TFV_ORDER" class="form-label">Tfv Order</label>
            <textarea id="TFV_ORDER" name="TFV_ORDER" class="form-control" rows="4">{{ old('TFV_ORDER', $item?->TFV_ORDER) }}</textarea>
            @error('TFV_ORDER')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.paramfnv_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
