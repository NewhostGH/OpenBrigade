@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: pdf.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Pdf Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.pdf.update', $itemKey) : route('legacy_migrated.pdf.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="pdf" class="form-label">Pdf</label>
            <input type="text" id="pdf" name="pdf" class="form-control" value="{{ old('pdf', $item?->pdf) }}">
            @error('pdf')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="id" class="form-label">Id</label>
            <input type="text" id="id" name="id" class="form-control" value="{{ old('id', $item?->id) }}">
            @error('id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="SelectionMail" class="form-label">Selectionmail</label>
            <input type="text" id="SelectionMail" name="SelectionMail" class="form-control" value="{{ old('SelectionMail', $item?->SelectionMail) }}">
            @error('SelectionMail')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.pdf.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
