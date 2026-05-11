@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: mail_create.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">MailCreate Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.mail_create.update', $itemKey) : route('legacy_migrated.mail_create.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="liste2" class="form-label">Liste2</label>
            <input type="text" id="liste2" name="liste2" class="form-control" value="{{ old('liste2', $item?->liste2) }}">
            @error('liste2')
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
            <label for="maxchar" class="form-label">Maxchar</label>
            <input type="text" id="maxchar" name="maxchar" class="form-control" value="{{ old('maxchar', $item?->maxchar) }}">
            @error('maxchar')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mode" class="form-label">Mode</label>
            <input type="text" id="mode" name="mode" class="form-control" value="{{ old('mode', $item?->mode) }}">
            @error('mode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mymessage" class="form-label">Mymessage</label>
            <textarea id="mymessage" name="mymessage" class="form-control" rows="4">{{ old('mymessage', $item?->mymessage) }}</textarea>
            @error('mymessage')
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


        <div class="mb-3">
            <label for="Messagesubject" class="form-label">Messagesubject</label>
            <input type="text" id="Messagesubject" name="Messagesubject" class="form-control" value="{{ old('Messagesubject', $item?->Messagesubject) }}">
            @error('Messagesubject')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="Messagebody" class="form-label">Messagebody</label>
            <input type="text" id="Messagebody" name="Messagebody" class="form-control" value="{{ old('Messagebody', $item?->Messagebody) }}">
            @error('Messagebody')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.mail_create.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
