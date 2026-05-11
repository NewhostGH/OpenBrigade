@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_info_participant.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementInfoParticipant Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_info_participant.update', $itemKey) : route('legacy_migrated.evenement_info_participant.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="EP_FLAG1" class="form-label">Ep Flag1</label>
            <input type="text" id="EP_FLAG1" name="EP_FLAG1" class="form-control" value="{{ old('EP_FLAG1', $item?->EP_FLAG1) }}">
            @error('EP_FLAG1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="p" class="form-label">P</label>
            <input type="text" id="p" name="p" class="form-control" value="{{ old('p', $item?->p) }}">
            @error('p')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="detail" class="form-label">Detail</label>
            <textarea id="detail" name="detail" class="form-control" rows="4">{{ old('detail', $item?->detail) }}</textarea>
            @error('detail')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_info_participant.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
