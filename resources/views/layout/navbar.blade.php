@php
    $topGroups = $legacyTopGroups ?? [];
@endphp

<div class="container-fluid noprint">
    <nav class="navbar navbar-expand-lg fixed-top noprint navbar-ebrigade">
        <div class="nav-left">
            <a class="navbar-brand logo-small" href="{{ route('dashboard') }}" title="Accueil">
                <i class="fas fa-home fa-lg" style="color: rgb(188, 188, 207);"></i>
            </a>
            <button class="navbar-toggler button-open noboxshadow" type="button" data-bs-toggle="collapse"
                data-bs-target="#navLateral" aria-controls="navLateral" aria-expanded="false">
                <span class="navbar-toggler-icon nav-picture"><i class="fa fa-bars py-1 text-violet"></i></span>
            </button>
            <div class="nav-center">
                @php
                    $uid = auth()->user()->P_ID ?? 0;
                @endphp
                @if (auth()->user()->hasPermission(41))
                    <a href="{{ url('/upd_personnel.php?from=default&tab=14&pompier=' . $uid . '&person=' . $uid . '&table=1') }}"
                        class="nav-text navtop-hover" title="Mes disponibilités" role="button">
                        <span class="navbar-toggler-icon nav-icon"><i class="fas fa-calendar-check fa-lg"></i></span>
                    </a>
                    <a href="{{ url('/upd_personnel.php?from=default&tab=16&pompier=' . $uid . '&table=1') }}"
                        class="nav-text navtop-hover" title="Mon calendrier" role="button">
                        <span class="navbar-toggler-icon nav-icon"><i class="fas fa-calendar fa-lg"></i></span>
                    </a>
                    <a href="{{ url('/evenement_choice.php?ec_mode=default&page=1') }}" class="nav-text navtop-hover"
                        title="Voir les activités prévues" role="button">
                        <span class="navbar-toggler-icon nav-icon"><i class="fas fa-calendar-days fa-lg"></i></span>
                    </a>
                @endif
                @if (auth()->user()->hasPermission(61))
                    <a href="{{ url('/tableau_garde.php') }}" class="nav-text navtop-hover" title="Tableau de garde"
                        role="button">
                        <span class="navbar-toggler-icon nav-icon"><i class="fas fa-clipboard-list fa-lg"></i></span>
                    </a>
                @endif
                <a href="{{ url('/search_personnel.php') }}" class="nav-text navtop-hover" title="Recherche personnel"
                    role="button">
                    <span class="navbar-toggler-icon nav-icon"><i class="fas fa-magnifying-glass fa-lg"></i></span>
                </a>
            </div>
        </div>
        <div class="nav-border">
            <button class="navbar-toggler custom-toggler button-left noboxshadow" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false">
                <i class="fas fa-angle-double-right py-1 text-violet"></i>
            </button>
        </div>
        <div class="collapse navbar-collapse nav-right" id="navbarMain">
            <ul class="navbar-nav nav-top">
                @foreach ($topGroups as $group)
                    @if (count($group['items']) > 0)
                        <li class="nav-item dropdown nav-top-item navtop-hover margin-li">
                            <a class="dropdown-toggle nav-link hover-white text-violet nodowntoggle" data-bs-toggle="dropdown"
                                href="#" title="{{ $group['name'] }}">
                                @if ($group['icon'] !== '')
                                    <i class="fas fa-{{ $group['icon'] }} fa-lg"></i>
                                @endif
                                {{ $group['name'] }}<i class="fas fa-chevron-down fa-xs"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                @foreach ($group['items'] as $item)
                                    <a class="dropdown-item dropdown-item-profil"
                                        href="{{ $item['external'] ? $item['url'] : url($item['url']) }}" @if ($item['external'])
                                        target="_blank" @endif>
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

                {{-- Quick-add "+" button --}}
                @php
                    $canAdd = auth()->user()->hasPermission(1)
                        || auth()->user()->hasPermission(15)
                        || auth()->user()->hasPermission(17)
                        || auth()->user()->hasPermission(70)
                        || auth()->user()->hasPermission(71);
                @endphp
                @if ($canAdd)
                    <li class="nav-item dropdown nav-top-item navtop-hover margin-li"
                        style="margin-left:10px; position:relative;">
                        <a class="nav-link hover-white text-violet nodowntoggle" data-bs-toggle="dropdown" href="#"
                            title="Ajout rapide" style="padding-top:7px;">
                            <span class="navbar-toggler-icon nav-icon fa-stack">
                                <i class="fas fa-plus-square fa-lg"></i>
                                <i class="fas fa-chevron-down fa-xs" style="font-size:0.6em;padding-bottom:2px;"></i>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            @if (auth()->user()->hasPermission(1))
                                <a class="dropdown-item dropdown-item-profil"
                                    href="{{ url('/ins_personnel.php?category=INT&suggestedcompany=-1') }}">
                                    <i class="fas fa-plus-circle" style="color:#28A745;"></i> Personnel
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission(15))
                                <a class="dropdown-item dropdown-item-profil"
                                    href="{{ url('/evenement_edit.php?action=create') }}">
                                    <i class="fas fa-plus-circle" style="color:#28A745;"></i> Activité
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission(17))
                                <a class="dropdown-item dropdown-item-profil" href="{{ url('/ins_vehicule.php') }}">
                                    <i class="fas fa-plus-circle" style="color:#28A745;"></i> Véhicule
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission(70))
                                <a class="dropdown-item dropdown-item-profil"
                                    href="{{ url('/ins_materiel.php?usage=ALL&type=ALL') }}">
                                    <i class="fas fa-plus-circle" style="color:#28A745;"></i> Matériel
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission(71))
                                <a class="dropdown-item dropdown-item-profil"
                                    href="{{ url('/upd_consommable.php?action=insert&type_conso=ALL') }}">
                                    <i class="fas fa-plus-circle" style="color:#28A745;"></i> Consommable
                                </a>
                            @endif
                        </div>
                    </li>
                @endif

                {{-- Help --}}
                <a class="nav-text navtop-hover" style="padding-top:7px;" href="{{ route('about') }}" title="A propos"
                    role="button">
                    <span class="navbar-toggler-icon nav-icon"><i class="far fa-question-circle fa-lg"></i></span>
                </a>

                {{-- User/profile --}}
                @php
                    $userPhoto = auth()->user()->P_PHOTO ?? '';
                    $userCivilite = (int) (auth()->user()->P_CIVILITE ?? 1);
                    if ($userPhoto !== '') {
                        $avatarSrc = '/trombinoscope/' . $userPhoto;
                    } elseif ($userCivilite === 2) {
                        $avatarSrc = asset('images/girl.png');
                    } else {
                        $avatarSrc = asset('images/boy.png');
                    }
                    $avatarFallback = asset('images/autre.png');
                @endphp
                <li class="nav-item dropdown nav-top-item navtop-hover margin-li">
                    <div class="dropdown-toggle nav-link hover-white text-violet nodowntoggle user-div"
                        data-bs-toggle="dropdown" href="#" title="Mon compte">
                        <div class="user-infos">
                            <p class="name">{{ auth()->user()->P_PRENOM ?? '' }}
                                {{ strtoupper(auth()->user()->P_NOM ?? '') }}
                            </p>
                        </div>
                        <div class="user-picture">
                            <img src="{{ $avatarSrc }}" class="profil-picture"
                                onerror="this.src='{{ $avatarFallback }}'">
                            <i class="ms-1 fas fa-chevron-down fa-xs"></i>
                        </div>
                    </div>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item dropdown-item-profil"
                            href="{{ route('personnel.show', auth()->user()->P_ID) }}">
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