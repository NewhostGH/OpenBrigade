@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: victimes.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Victimes Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.victimes.update', $itemKey) : route('legacy_migrated.victimes.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="regulated" class="form-label">Regulated</label>
            <input type="text" id="regulated" name="regulated" class="form-control" value="{{ old('regulated', $item?->regulated) }}">
            @error('regulated')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_in" class="form-label">Date In</label>
            <input type="text" id="date_in" name="date_in" class="form-control" value="{{ old('date_in', $item?->date_in) }}">
            @error('date_in')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="time_in" class="form-label">Time In</label>
            <input type="text" id="time_in" name="time_in" class="form-control" value="{{ old('time_in', $item?->time_in) }}">
            @error('time_in')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_out" class="form-label">Date Out</label>
            <input type="text" id="date_out" name="date_out" class="form-control" value="{{ old('date_out', $item?->date_out) }}">
            @error('date_out')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="time_out" class="form-label">Time Out</label>
            <input type="text" id="time_out" name="time_out" class="form-control" value="{{ old('time_out', $item?->time_out) }}">
            @error('time_out')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date Naissance</label>
            <input type="text" id="date_naissance" name="date_naissance" class="form-control" value="{{ old('date_naissance', $item?->date_naissance) }}">
            @error('date_naissance')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="age" class="form-label">Age</label>
            <input type="text" id="age" name="age" class="form-control" value="{{ old('age', $item?->age) }}">
            @error('age')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="numerotation" class="form-label">Numerotation</label>
            <input type="text" id="numerotation" name="numerotation" class="form-control" value="{{ old('numerotation', $item?->numerotation) }}">
            @error('numerotation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $item?->address) }}">
            @error('address')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="comptage" class="form-label">Comptage</label>
            <input type="text" id="comptage" name="comptage" class="form-control" value="{{ old('comptage', $item?->comptage) }}">
            @error('comptage')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="detresse_vitale" class="form-label">Detresse Vitale</label>
            <input type="text" id="detresse_vitale" name="detresse_vitale" class="form-control" value="{{ old('detresse_vitale', $item?->detresse_vitale) }}">
            @error('detresse_vitale')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="soins" class="form-label">Soins</label>
            <input type="text" id="soins" name="soins" class="form-control" value="{{ old('soins', $item?->soins) }}">
            @error('soins')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="decede" class="form-label">Decede</label>
            <input type="text" id="decede" name="decede" class="form-control" value="{{ old('decede', $item?->decede) }}">
            @error('decede')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="malaise" class="form-label">Malaise</label>
            <input type="text" id="malaise" name="malaise" class="form-control" value="{{ old('malaise', $item?->malaise) }}">
            @error('malaise')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="traumatisme" class="form-label">Traumatisme</label>
            <input type="text" id="traumatisme" name="traumatisme" class="form-control" value="{{ old('traumatisme', $item?->traumatisme) }}">
            @error('traumatisme')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="medicalise" class="form-label">Medicalise</label>
            <input type="text" id="medicalise" name="medicalise" class="form-control" value="{{ old('medicalise', $item?->medicalise) }}">
            @error('medicalise')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="vetements" class="form-label">Vetements</label>
            <input type="text" id="vetements" name="vetements" class="form-control" value="{{ old('vetements', $item?->vetements) }}">
            @error('vetements')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="alimentation" class="form-label">Alimentation</label>
            <input type="text" id="alimentation" name="alimentation" class="form-control" value="{{ old('alimentation', $item?->alimentation) }}">
            @error('alimentation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="information" class="form-label">Information</label>
            <input type="text" id="information" name="information" class="form-control" value="{{ old('information', $item?->information) }}">
            @error('information')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="refus" class="form-label">Refus</label>
            <input type="text" id="refus" name="refus" class="form-control" value="{{ old('refus', $item?->refus) }}">
            @error('refus')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.victimes.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
