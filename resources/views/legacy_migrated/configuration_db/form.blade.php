@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: configuration_db.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ConfigurationDb Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.configuration_db.update', $itemKey) : route('legacy_migrated.configuration_db.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="save" name="save" value="{{ old('save', $item?->save) }}">


        <div class="mb-3">
            <label for="server" class="form-label">Server</label>
            <input type="text" id="server" name="server" class="form-control" value="{{ old('server', $item?->server) }}">
            @error('server')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="user" class="form-label">User</label>
            <input type="text" id="user" name="user" class="form-control" value="{{ old('user', $item?->user) }}">
            @error('user')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="text" id="password" name="password" class="form-control" value="{{ old('password', $item?->password) }}">
            @error('password')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="database" class="form-label">Database</label>
            <input type="text" id="database" name="database" class="form-control" value="{{ old('database', $item?->database) }}">
            @error('database')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.configuration_db.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
