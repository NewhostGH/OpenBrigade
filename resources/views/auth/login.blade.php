@extends('layout.app')

@section('title', 'Connexion – ' . config('app.name'))

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">{{ config('app.name') }}</h4>

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="login" class="form-label">Identifiant / Email</label>
                        <input
                            id="login"
                            type="text"
                            name="login"
                            class="form-control @error('login') is-invalid @enderror"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            autocomplete="username"
                        >
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                            autocomplete="current-password"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            Connexion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
