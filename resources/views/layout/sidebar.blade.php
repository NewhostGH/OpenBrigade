<div class="col-1 ob-col-lateral noprint">
    <nav class="navbar navbar-expand-lg ob-navbar-lateral" style="width:220px; overflow:hidden">
        <div class="ob-div-scroll">
            <ul class="nav flex-column ob-nav-lateral collapse navbar-collapse noprint" id="navLateral">
                <a class="ob-nav-logo ob-logo-lateral" href="{{ route('dashboard') }}" title="Accueil">
                    @if (isset($appIdentity) && $appIdentity->logoUrl())
                        <img height="32" width="32" src="{{ $appIdentity->logoUrl() }}" alt="">
                    @else
                        <img height="32" width="32" src="{{ asset('images/logo.png') }}"
                             onerror="this.style.display='none'">
                    @endif
                    <span>{{ isset($appIdentity) ? $appIdentity->shortName() : config('app.name') }}</span>
                </a>

                {{-- ── Sidebar search ─────────────────────────────────── --}}
                <div class="ob-sidebar-search-wrap">
                    <div class="ob-sidebar-search-inner">
                        <i class="fas fa-search ob-sidebar-search-icon" aria-hidden="true"></i>
                        <input type="search"
                               id="sidebarSearch"
                               class="ob-sidebar-search-input"
                               placeholder="Rechercher…"
                               autocomplete="off"
                               spellcheck="false"
                               aria-label="Rechercher dans le menu">
                        <button type="button"
                                id="sidebarSearchClear"
                                class="ob-sidebar-search-clear d-none"
                                aria-label="Effacer la recherche">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                @foreach ($navGroups ?? [] as $group)
                    <li class="nav-item ob-item-lateral mouseMenu">
                        <a class="nav-link ob-dropdown-lateral" href="#menu-{{ $group['code'] }}"
                            data-bs-toggle="collapse"
                            aria-expanded="{{ $group['active'] ? 'true' : 'false' }}">
                            <i class="fas fa-{{ $group['icon'] }} ob-icon-lateral"></i>
                            <span>{{ $group['label'] }}</span>
                        </a>
                        <div class="collapse ob-div-lateral {{ $group['active'] ? 'show' : '' }}"
                            id="menu-{{ $group['code'] }}">
                            @foreach ($group['items'] as $item)
                                @if ($item === null)
                                    <hr class="ob-sidebar-divider">
                                @else
                                    <div class="ob-sidebar-item-row {{ $item['active'] ? 'sidebar-item-row-active' : '' }}">
                                        <a class="nav-link ob-link-lateral {{ $item['active'] ? 'ob-link-lateral-active' : '' }}"
                                            href="{{ url($item['url']) }}">
                                            @if ($item['icon'] !== '')
                                                <i class="fas fa-{{ $item['icon'] }} fa-fw ob-sidebar-item-icon"></i>
                                            @endif
                                            {{ $item['label'] }}
                                        </a>
                                        <button class="ob-sidebar-pin-btn {{ $item['pinned'] ? 'pinned' : '' }}"
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
        <div class="ob-collapse-menu"><i class="fas fa-angle-double-left"></i> Réduire le menu</div>
        <div class="ob-decollapse-menu" style="display:none;"><i class="fas fa-angle-double-right ob-icon-collapse"></i></div>
    </nav>
</div>
