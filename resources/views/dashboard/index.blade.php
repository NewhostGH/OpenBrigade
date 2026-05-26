@extends('layout.app')

@section('title', 'Tableau de bord - ' . config('app.name'))

@section('content')
    <div class="container-fluid px-3 pt-3">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h4 mb-2">Bienvenue sur {{ config('app.name') }}</h1>
                        <p class="text-muted mb-0">
                            Le flux d'authentification Laravel est actif. Les modules metier seront ajoutes ici
                            progressivement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection