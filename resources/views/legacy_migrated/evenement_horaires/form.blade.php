@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_horaires.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementHoraires Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_horaires.update', $itemKey) : route('legacy_migrated.evenement_horaires.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="pid" name="pid" value="{{ old('pid', $item?->pid) }}">

<input type="hidden" id="vid" name="vid" value="{{ old('vid', $item?->vid) }}">


        <div class="mb-3">
            <label for="debut_$k" class="form-label">Debut $K</label>
            <textarea id="debut_$k" name="debut_$k" class="form-control" rows="4">{{ old('debut_$k', $item?->debut_$k) }}</textarea>
            @error('debut_$k')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fin_$k" class="form-label">Fin $K</label>
            <textarea id="fin_$k" name="fin_$k" class="form-control" rows="4">{{ old('fin_$k', $item?->fin_$k) }}</textarea>
            @error('fin_$k')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_horaires.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
