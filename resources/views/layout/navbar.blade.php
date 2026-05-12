@php($topGroups = $legacyTopGroups ?? [])

<div class="container-fluid noprint">
<nav class="navbar navbar-expand-lg fixed-top noprint navbar-ebrigade">
    <div class="nav-left">
        <a class="navbar-brand logo-small" href="{{ route('dashboard') }}" title="Accueil">
            <i class="fas fa-home fa-lg" style="color: rgb(188, 188, 207);"></i>
        </a>
        <button class="navbar-toggler button-open noboxshadow" type="button" data-toggle="collapse" data-target="#navLateral" aria-controls="navLateral" aria-expanded="false">
            <span class="navbar-toggler-icon nav-picture"><i class="fa fa-bars py-1 text-violet"></i></span>
        </button>
        <div class="nav-center">
            <a href="{{ url('/index_d.php') }}" class="nav-text navtop-hover" title="Accueil legacy" role="button">
                <span class="navbar-toggler-icon nav-icon"><i class="fas fa-users fa-lg"></i></span>
            </a>
        </div>
    </div>
    <div class="nav-border">
        <button class="navbar-toggler custom-toggler button-left noboxshadow" type="button" data-toggle="collapse" data-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false">
            <i class="fas fa-angle-double-right py-1 text-violet"></i>
        </button>
    </div>
    <div class="collapse navbar-collapse nav-right" id="navbarMain">
        <ul class="navbar-nav nav-top">
            @foreach ($topGroups as $group)
                @if (count($group['items']) > 0)
                    <li class="nav-item dropdown nav-top-item navtop-hover margin-li">
                        <a class="dropdown-toggle nav-link hover-white text-violet nodowntoggle" data-toggle="dropdown" href="#" title="{{ $group['name'] }}">
                            @if ($group['icon'] !== '')
                                <i class="fas fa-{{ $group['icon'] }} fa-lg"></i>
                            @endif
                            {{ $group['name'] }}<i class="fas fa-chevron-down fa-xs"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            @foreach ($group['items'] as $item)
                                <a class="dropdown-item dropdown-item-profil" href="{{ $item['external'] ? $item['url'] : url($item['url']) }}" @if ($item['external']) target="_blank" @endif>
                                    @if ($item['icon'] !== '')
                                        <i class="fa fa-{{ $item['icon'] }} fa-lg"></i>
                                    @endif
                                    {{ $item['name'] }}
                                </a>
                            @endforeach
                        </div>
                    </li>
                @endif
            @endforeach

            {{-- Help --}}
            <a class="nav-text navtop-hover" style="padding-top:7px;" href="{{ route('about') }}" title="A propos" role="button">
                <span class="navbar-toggler-icon nav-icon"><i class="far fa-question-circle fa-lg"></i></span>
            </a>

            {{-- User/profile --}}
            <li class="nav-item dropdown nav-top-item navtop-hover margin-li">
                <div class="dropdown-toggle nav-link hover-white text-violet nodowntoggle user-div" data-toggle="dropdown" href="#" title="Mon compte">
                    <div class="user-infos">
                        <p class="name">{{ auth()->user()->P_PRENOM ?? '' }} {{ auth()->user()->P_NOM ?? '' }}</p>
                    </div>
                    <div class="user-picture">
                        <i class="fas fa-user profil-picture" style="border:2px white solid;padding:3px;border-radius:15px;"></i>
                        <i class="ml-1 fas fa-chevron-down fa-xs"></i>
                    </div>
                </div>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item dropdown-item-profil" href="{{ route('personnel.show', auth()->user()->P_ID) }}">
                        <i class="fas fa-user fa-lg"></i> Ma fiche
                    </a>
                    <div role="separator" class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-item-profil">
                            <i class="fa fa-power-off fa-lg" style="color:red;"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
</div>
