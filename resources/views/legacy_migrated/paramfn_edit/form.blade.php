@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: paramfn_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ParamfnEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.paramfn_edit.update', $itemKey) : route('legacy_migrated.paramfn_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="TP_ID" name="TP_ID" value="{{ old('TP_ID', $item?->TP_ID) }}">

<input type="hidden" id="filter" name="filter" value="{{ old('filter', $item?->filter) }}">


        <div class="mb-3">
            <label for="TP_LIBELLE" class="form-label">Tp Libelle</label>
            <input type="text" id="TP_LIBELLE" name="TP_LIBELLE" class="form-control" value="{{ old('TP_LIBELLE', $item?->TP_LIBELLE) }}">
            @error('TP_LIBELLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="INSTRUCTOR" class="form-label">Instructor</label>
            <input type="text" id="INSTRUCTOR" name="INSTRUCTOR" class="form-control" value="{{ old('INSTRUCTOR', $item?->INSTRUCTOR) }}">
            @error('INSTRUCTOR')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_CODE" class="form-label">Te Code</label>
            <textarea id="TE_CODE" name="TE_CODE" class="form-control" rows="4">{{ old('TE_CODE', $item?->TE_CODE) }}</textarea>
            @error('TE_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_ID" class="form-label">Eq Id</label>
            <textarea id="EQ_ID" name="EQ_ID" class="form-control" rows="4">{{ old('EQ_ID', $item?->EQ_ID) }}</textarea>
            @error('EQ_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TP_NUM" class="form-label">Tp Num</label>
            <textarea id="TP_NUM" name="TP_NUM" class="form-control" rows="4">{{ old('TP_NUM', $item?->TP_NUM) }}</textarea>
            @error('TP_NUM')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_ID" class="form-label">Ps Id</label>
            <textarea id="PS_ID" name="PS_ID" class="form-control" rows="4">{{ old('PS_ID', $item?->PS_ID) }}</textarea>
            @error('PS_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_ID2" class="form-label">Ps Id2</label>
            <textarea id="PS_ID2" name="PS_ID2" class="form-control" rows="4">{{ old('PS_ID2', $item?->PS_ID2) }}</textarea>
            @error('PS_ID2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.paramfn_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
