<div class="col-1 col-lateral noprint">
    <nav class="navbar navbar-expand-lg navbar-lateral" style="width:220px; overflow:hidden">
        <div class="div-scroll">
            <ul class="nav flex-column nav-lateral collapse navbar-collapse noprint" id="navLateral">
                <a class="nav-logo logo-lateral" href="{{ route('dashboard') }}" title="Accueil">
                    <img height="32" width="32" src="{{ asset('images/logo.png') }}"
                         onerror="this.style.display='none'">
                    <span>{{ config('app.name') }}</span>
                </a>

                @foreach ($navGroups ?? [] as $group)
                    <li class="nav-item item-lateral mouseMenu">
                        <a class="nav-link dropdown-lateral" href="#menu-{{ $group['code'] }}"
                            data-bs-toggle="collapse"
                            aria-expanded="{{ $group['active'] ? 'true' : 'false' }}">
                            <i class="fas fa-{{ $group['icon'] }} icon-lateral"></i>
                            <span>{{ $group['label'] }}</span>
                        </a>
                        <div class="collapse div-lateral {{ $group['active'] ? 'show' : '' }}"
                            id="menu-{{ $group['code'] }}">
                            @foreach ($group['items'] as $item)
                                @if ($item === null)
                                    <hr class="sidebar-divider">
                                @else
                                    <div class="sidebar-item-row {{ $item['active'] ? 'sidebar-item-row-active' : '' }}">
                                        <a class="nav-link link-lateral {{ $item['active'] ? 'link-lateral-active' : '' }}"
                                            href="{{ url($item['url']) }}">
                                            @if ($item['icon'] !== '')
                                                <i class="fas fa-{{ $item['icon'] }} fa-fw sidebar-item-icon"></i>
                                            @endif
                                            {{ $item['label'] }}
                                        </a>
                                        <button class="sidebar-pin-btn {{ $item['pinned'] ? 'pinned' : '' }}"
                                            data-key="{{ $item['key'] }}"
                                            title="{{ $item['pinned'] ? 'Retirer du raccourci' : 'Épingler dans la barre' }}"
                                            aria-label="{{ $item['pinned'] ? 'Désépingler' : 'Épingler' }}">
                                            <i class="fas fa-thumbtack"></i>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="collapse-menu"><i class="fas fa-angle-double-left"></i> Réduire le menu</div>
        <div class="decollapse-menu" style="display:none;"><i class="fas fa-angle-double-right icon-collapse"></i></div>
    </nav>
</div>
