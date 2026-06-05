<div class="container-fluid noprint">
    <nav class="navbar navbar-expand-lg fixed-top noprint ob-navbar-ebrigade">

        {{-- Left: brand + sidebar toggle --}}
        <div class="ob-nav-left">
            <a class="navbar-brand ob-logo-small" href="{{ route('dashboard') }}" title="Accueil">
                <i class="fas fa-home fa-lg" style="color: rgb(188, 188, 207);"></i>
            </a>
            <button class="navbar-toggler button-open noboxshadow" type="button" data-bs-toggle="collapse"
                data-bs-target="#navLateral" aria-controls="navLateral" aria-expanded="false">
                <span class="navbar-toggler-icon nav-picture"><i class="fa fa-bars py-1 ob-text-violet"></i></span>
            </button>
        </div>

        {{-- Centre: user-pinned siglets --}}
        <div class="ob-nav-siglets" id="navSiglets">
            @forelse ($pinnedShortcuts ?? [] as $shortcut)
                <a class="ob-siglet" href="{{ url($shortcut['url']) }}" title="{{ $shortcut['label'] }}"
                    data-key="{{ $shortcut['key'] }}">
                    @if ($shortcut['icon'] !== '')
                        <i class="fas fa-{{ $shortcut['icon'] }}"></i>
                    @endif
                    <span>{{ $shortcut['label'] }}</span>
                    <button class="ob-siglet-unpin" data-key="{{ $shortcut['key'] }}" title="Désépingler" aria-label="Désépingler">×</button>
                </a>
            @empty
                <span class="ob-siglets-hint">Épinglez des raccourcis depuis le menu latéral <i class="fas fa-thumbtack fa-xs"></i></span>
            @endforelse
        </div>

        <div class="ob-nav-border">
            <button class="navbar-toggler ob-custom-toggler ob-button-left noboxshadow" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false">
                <i class="fas fa-chevron-down py-1 ob-text-violet"></i>
            </button>
        </div>

        {{-- Right: actions + user menu --}}
        <div class="collapse navbar-collapse ob-nav-right" id="navbarMain">
            <ul class="navbar-nav nav-top">

                {{-- Quick-add "+" button --}}
                @php
                    $canAdd = auth()->user()->hasPermission(1)
                        || auth()->user()->hasPermission(15)
                        || auth()->user()->hasPermission(17)
                        || auth()->user()->hasPermission(70)
                        || auth()->user()->hasPermission(71);
                @endphp
                @if ($canAdd)
                    <li class="nav-item dropdown nav-top-item ob-navtop-hover ob-margin-li" style="position:relative;">
                        <a class="nav-link ob-hover-white ob-text-violet ob-nodowntoggle" data-bs-toggle="dropdown" href="#"
                            title="Ajout rapide" aria-expanded="false">
                            <span class="navbar-toggler-icon ob-nav-icon">
                                <i class="fas fa-plus-square fa-lg"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end ob-nav-dropdown-menu">
                            @if (auth()->user()->hasPermission(1))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil"
                                        href="{{ url('/ins_personnel.php?category=INT&suggestedcompany=-1') }}">
                                        <i class="fas fa-user-plus fa-fw ob-nav-item-icon" style="color:#28A745;"></i> Personnel
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(15))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil"
                                        href="{{ url('/evenement_edit.php?action=create') }}">
                                        <i class="fas fa-calendar-plus fa-fw ob-nav-item-icon" style="color:#28A745;"></i> Activité
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(17))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil" href="{{ url('/ins_vehicule.php') }}">
                                        <i class="fas fa-truck fa-fw ob-nav-item-icon" style="color:#28A745;"></i> Véhicule
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(70))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil"
                                        href="{{ url('/ins_materiel.php?usage=ALL&type=ALL') }}">
                                        <i class="fas fa-toolbox fa-fw ob-nav-item-icon" style="color:#28A745;"></i> Matériel
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(71))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil"
                                        href="{{ url('/upd_consommable.php?action=insert&type_conso=ALL') }}">
                                        <i class="fas fa-boxes fa-fw ob-nav-item-icon" style="color:#28A745;"></i> Consommable
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Help --}}
                <ul class="ob-nav-text ob-navtop-hover ob-margin-li pt-2" href="{{ route('about') }}" title="A propos" role="button">
                    <span class="navbar-toggler-icon ob-nav-icon"><i class="far fa-question-circle fa-lg"></i></span>
                </ul>

                {{-- User/profile --}}
                @php
                    $user = auth()->user();
                    $avatarSrc = $user->getAvatarUrl();
                    $avatarFallback = asset('images/autre.png');
                @endphp
                <li class="nav-item dropdown nav-top-item ob-navtop-hover ob-margin-li">
                    <div class="dropdown-toggle nav-link ob-hover-white ob-text-violet ob-nodowntoggle ob-user-div"
                        data-bs-toggle="dropdown" title="Mon compte">
                        <div class="ob-user-infos">
                            <p class="ob-user-name">{{ auth()->user()->P_PRENOM ?? '' }}
                                {{ strtoupper(auth()->user()->P_NOM ?? '') }}
                            </p>
                        </div>
                        <div class="ob-user-picture">
                            <img src="{{ $avatarSrc }}" class="ob-profil-picture"
                                onerror="this.src='{{ $avatarFallback }}'">
                            <i class="ms-1 fas fa-chevron-down fa-xs"></i>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end ob-nav-dropdown-menu">
                        <li>
                            <a class="dropdown-item dropdown-item-profil"
                                href="{{ route('personnel.show', auth()->user()->P_ID) }}">
                                <i class="fas fa-user fa-fw ob-nav-item-icon"></i> Ma fiche
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil"
                                href="{{ url('/legacy/change_password.php') }}">
                                <i class="fas fa-key fa-fw ob-nav-item-icon"></i> Mot de passe
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil"
                                href="{{ url('/legacy/preferences.php') }}">
                                <i class="fas fa-sliders-h fa-fw ob-nav-item-icon"></i> Mes préférences
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil"
                                href="{{ url('/legacy/upd_section.php?S_ID=' . (auth()->user()->P_SECTION ?? 0)) }}">
                                <i class="fas fa-building fa-fw ob-nav-item-icon"></i> Ma section
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item dropdown-item-profil">
                                    <i class="fa fa-power-off fa-fw ob-nav-item-icon" style="color:red;"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</div>
