<div class="col-1 col-lateral noprint">
    <nav class="navbar navbar-expand-lg navbar-lateral" style="width:220px; overflow:hidden">
        <div class="div-scroll">
            <ul class="nav flex-column nav-lateral collapse navbar-collapse noprint" id="navLateral">
                <a class="navbar-brand nav-logo logo-lateral" href="{{ route('dashboard') }}" title="Accueil">
                    <img style="margin-right:5px;" height="40" width="40" src="{{ asset('legacy-assets/images/logov3.png') }}" onerror="this.style.display='none'">
                    {{ config('app.name') }}
                </a>

                {{-- Personnel --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-PERSO" data-toggle="collapse" aria-expanded="{{ request()->routeIs('personnel.*') ? 'true' : 'false' }}">
                        <i class="far fa-user icon-lateral"></i><span>Personnel</span>
                    </a>
                    <div class="collapse div-lateral {{ request()->routeIs('personnel.*') ? 'show' : '' }}" id="menu-PERSO">
                        <a class="nav-link link-lateral {{ request()->routeIs('personnel.index') ? 'menu-actif' : '' }}" href="{{ route('personnel.index') }}">Liste</a>
                    </div>
                </li>

                {{-- Activité --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-ACT" data-toggle="collapse" aria-expanded="false">
                        <i class="far fa-calendar-alt icon-lateral"></i><span>Activité</span>
                    </a>
                    <div class="collapse div-lateral" id="menu-ACT">
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=evenement_choice.php%3Fec_mode%3Ddefault%26page%3D1">Liste</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=evenement_choice.php%3Fec_mode%3DMC%26page%3D1">Main Courante</a>
                    </div>
                </li>

                {{-- Garde --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-GAR" data-toggle="collapse" aria-expanded="false">
                        <i class="far fa-clipboard-list icon-lateral"></i><span>Garde</span>
                    </a>
                    <div class="collapse div-lateral" id="menu-GAR">
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=tableau_garde.php">Tableau Garde</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=feuille_garde.php%3Fevenement%3D0%26from%3Dgardes">Garde du jour</a>
                    </div>
                </li>

                {{-- Inventaire / Logistique --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-VEH" data-toggle="collapse" aria-expanded="false">
                        <i class="far fa-dot-circle icon-lateral"></i><span>Logistique</span>
                    </a>
                    <div class="collapse div-lateral" id="menu-VEH">
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=vehicule.php%3Fpage%3D1">Véhicules</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=materiel.php%3Fpage%3D1">Matériels</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=consommable.php%3Fpage%3D1">Consommables</a>
                    </div>
                </li>

                {{-- Documents --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-DOC" data-toggle="collapse" aria-expanded="false">
                        <i class="far fa-file icon-lateral"></i><span>Document</span>
                    </a>
                    <div class="collapse div-lateral" id="menu-DOC">
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=documents.php%3Ftd%3DALL%26page%3D1%26yeardoc%3Dall%26dossier%3D0">Bibliothèque</a>
                    </div>
                </li>

                {{-- Communication --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-COMM" data-toggle="collapse" aria-expanded="false">
                        <i class="far fa-envelope icon-lateral"></i><span>Communication</span>
                    </a>
                    <div class="collapse div-lateral" id="menu-COMM">
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=chat.php">Chat</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=mail_create.php">Message</a>
                    </div>
                </li>

                {{-- Organisation --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-ORGA" data-toggle="collapse" aria-expanded="false">
                        <i class="far fa-building icon-lateral"></i><span>Organisation</span>
                    </a>
                    <div class="collapse div-lateral" id="menu-ORGA">
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=departement.php">Sections</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=section.php">Organigramme</a>
                    </div>
                </li>

                {{-- Configuration --}}
                <li class="nav-item item-lateral mouseMenu">
                    <a class="nav-link dropdown-lateral" href="#menu-ADMIN" data-toggle="collapse" aria-expanded="false">
                        <i class="far fa-sun icon-lateral"></i><span>Configuration</span>
                    </a>
                    <div class="collapse div-lateral" id="menu-ADMIN">
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=configuration.php">Général</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=parametrage.php">Paramétrage</a>
                        <a class="nav-link link-lateral" href="{{ route('dashboard.legacy') }}?redirect=habilitations.php">Habilitations</a>
                    </div>
                </li>

            </ul>
        </div>
        <div class="collapse-menu"><i class="fas fa-angle-double-left"></i> Réduire le menu</div>
        <div class="decollapse-menu" style="display:none;"><i class="fas fa-angle-double-right icon-collapse"></i></div>
    </nav>
</div>
