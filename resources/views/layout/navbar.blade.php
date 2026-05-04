@auth
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2B2350;">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">
            {{ config('app.name') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        {{-- Navigation items are added incrementally as modules are migrated --}}
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link">
                            <i class="fa fa-sign-out"></i> Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
@endauth
