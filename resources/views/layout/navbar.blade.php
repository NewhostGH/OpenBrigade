<div class="container-fluid noprint">
    <nav class="navbar navbar-expand-lg fixed-top noprint ob-navbar-ebrigade">

        {{-- Left: brand + sidebar toggle --}}
        <div class="ob-nav-left">
            <a class="navbar-brand ob-logo-small" href="{{ route('dashboard') }}" title="{{ __('nav.home') }}">
                <i class="fas fa-home fa-lg" style="color:var(--brand-text);"></i>
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
                    <button class="ob-siglet-unpin" data-key="{{ $shortcut['key'] }}" title="{{ __('nav.unpin_aria') }}"
                        aria-label="{{ __('nav.unpin_aria') }}">×</button>
                </a>
            @empty
                <span class="ob-siglets-hint">{{ __('nav.shortcuts_hint') }} <i
                        class="fas fa-thumbtack fa-xs"></i></span>
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

                {{-- Active section + role context switchers (always visible) --}}
                @php
                    $ctxSections = $ctxSections ?? collect();
                    $ctxRoles = $ctxRoles ?? collect();
                    $activeSectionLabel = $ctxActiveSection !== null
                        ? (optional($ctxSections->firstWhere('S_ID', $ctxActiveSection))->S_DESCRIPTION ?? __('nav.section_fallback'))
                        : __('common.all');
                    $activeRoleLabel = $ctxActiveRole
                        ? (optional($ctxRoles->firstWhere('id', $ctxActiveRole))->name ?? __('nav.role_fallback'))
                        : __('nav.all_my_roles');
                @endphp
                @feature('multi_site')
                <li class="nav-item dropdown nav-top-item ob-navtop-hover ob-margin-li">
                    <a class="nav-link ob-hover-white ob-text-violet ob-nodowntoggle" data-bs-toggle="dropdown" href="#"
                        title="{{ __('nav.active_section') }}" aria-expanded="false">
                        <i class="fas fa-sitemap me-1"></i><span class="ob-ctx-label">{{ $activeSectionLabel }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end ob-nav-dropdown-menu">
                        <li>
                            <h6 class="dropdown-header">{{ __('nav.active_section') }}</h6>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil {{ $ctxActiveSection === null ? 'active' : '' }}"
                                href="{{ route('context.section', ['s' => 'all']) }}">
                                <i class="fas fa-layer-group fa-fw ob-nav-item-icon"></i>
                                {{ __('nav.all_my_sections') }}
                                @if ($ctxActiveSection === null)<i class="fas fa-check ms-2 ob-nav-check"></i>@endif
                            </a>
                        </li>
                        @forelse ($ctxSections as $s)
                            @php
                                $depth = (int) ($s->depth ?? 0);
                                $isOrgRoot = (int) $s->S_ID === 0;          // the organizational root
                                $isRoot = (int) ($s->S_PARENT ?? 0) === 0;  // a top-level site
                            @endphp
                            <li>
                                <a class="dropdown-item dropdown-item-profil {{ $isOrgRoot || $isRoot ? 'ob-nav-section-root' : '' }} {{ (int) $s->S_ID === (int) $ctxActiveSection ? 'active' : '' }}"
                                    href="{{ route('context.section', ['s' => $s->S_ID]) }}"
                                    style="padding-left: {{ 1 + $depth * 1.1 }}rem;">
                                    @if ($isOrgRoot)
                                        <i class="fas fa-sitemap fa-fw ob-nav-item-icon"></i>
                                    @elseif ($depth === 0)
                                        <i class="fas fa-building fa-fw ob-nav-item-icon"></i>
                                    @else
                                        <span class="ob-nav-item-icon fa-fw ob-nav-muted" aria-hidden="true">└</span>
                                    @endif
                                    {{ $s->S_DESCRIPTION }}
                                    @if ($isOrgRoot)
                                        <span class="ob-nav-section-root-badge">{{ __('nav.section_badge_org') }}</span>
                                    @elseif ($isRoot)
                                        <span class="ob-nav-section-root-badge">{{ __('nav.section_badge_site') }}</span>
                                    @elseif (!empty($s->S_CODE))
                                        <span class="ob-nav-muted ms-1"
                                            style="font-size:var(--font-size-xs);">{{ $s->S_CODE }}</span>
                                    @endif
                                    @if ((int) $s->S_ID === (int) $ctxActiveSection)<i
                                    class="fas fa-check ms-2 ob-nav-check"></i>@endif
                                </a>
                            </li>
                        @empty
                            <li><span class="dropdown-item-text ob-nav-muted small">{{ __('nav.no_section_assigned') }}</span></li>
                        @endforelse
                    </ul>
                </li>
                @endfeature

                <li class="nav-item dropdown nav-top-item ob-navtop-hover ob-margin-li">
                    <a class="nav-link ob-hover-white ob-text-violet ob-nodowntoggle" data-bs-toggle="dropdown" href="#"
                        title="{{ __('nav.active_role') }}" aria-expanded="false">
                        <i class="fas fa-user-tie me-1"></i><span class="ob-ctx-label">{{ $activeRoleLabel }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end ob-nav-dropdown-menu">
                        <li>
                            <h6 class="dropdown-header">{{ __('nav.active_role') }}</h6>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil {{ $ctxActiveRole ? '' : 'active' }}"
                                href="{{ route('context.role', ['r' => 'all']) }}">
                                {{ __('nav.all_my_roles') }}
                                @unless ($ctxActiveRole)<i class="fas fa-check ms-2 ob-nav-check"></i>@endunless
                            </a>
                        </li>
                        @foreach ($ctxRoles as $r)
                            <li>
                                <a class="dropdown-item dropdown-item-profil {{ (int) $r->id === (int) $ctxActiveRole ? 'active' : '' }}"
                                    href="{{ route('context.role', ['r' => $r->id]) }}">
                                    {{ $r->name }}
                                    @if (!empty($r->inherited))<span class="ob-badge ob-badge-int ms-1"
                                    style="font-size:9px;">{{ __('nav.role_inherited') }}</span>@endif
                                    @if ((int) $r->id === (int) $ctxActiveRole)<i
                                    class="fas fa-check ms-2 ob-nav-check"></i>@endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>

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
                            title="{{ __('nav.quick_add') }}" aria-expanded="false">
                            <span class="navbar-toggler-icon ob-nav-icon">
                                <i class="fas fa-plus-square fa-lg"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end ob-nav-dropdown-menu">
                            @if (auth()->user()->hasPermission(1))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil" href="{{ route('personnel.create') }}">
                                        <i class="fas fa-user-plus fa-fw ob-nav-item-icon"
                                            style="color:var(--color-nav-add);"></i> {{ __('nav.add_personnel') }}
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(15))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil" href="{{ route('event.create') }}">
                                        <i class="fas fa-calendar-plus fa-fw ob-nav-item-icon"
                                            style="color:var(--color-nav-add);"></i> {{ __('nav.add_event') }}
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(17))
                                <li>
                                    <a class="dropdown-item dropdown-item-profil" href="{{ route('vehicle.create') }}">
                                        <i class="fas fa-truck fa-fw ob-nav-item-icon" style="color:var(--color-nav-add);"></i>
                                        {{ __('nav.add_vehicle') }}
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(70))
                                <li>
                                    {{-- TODO: Migrate code — ins_equipment.php has no native route yet --}}
                                    <a class="dropdown-item dropdown-item-profil"
                                        href="{{ url('/legacy/ins_equipment.php?usage=ALL&type=ALL') }}">
                                        <i class="fas fa-toolbox fa-fw ob-nav-item-icon"
                                            style="color:var(--color-nav-add);"></i> {{ __('nav.add_equipment') }}
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission(71))
                                <li>
                                    {{-- TODO: Migrate code — upd_consumable.php has no native route yet --}}
                                    <a class="dropdown-item dropdown-item-profil"
                                        href="{{ url('/legacy/upd_consumable.php?action=insert&type_conso=ALL') }}">
                                        <i class="fas fa-boxes fa-fw ob-nav-item-icon" style="color:var(--color-nav-add);"></i>
                                        {{ __('nav.add_consumable') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Help --}}
                <ul class="ob-nav-text ob-navtop-hover ob-margin-li pt-2" href="{{ route('about') }}" title="{{ __('nav.help') }}"
                    role="button">
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
                        data-bs-toggle="dropdown" title="{{ __('nav.my_account') }}">
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
                                <i class="fas fa-user fa-fw ob-nav-item-icon"></i> {{ __('nav.my_profile') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil" href="{{ route('my-permissions') }}">
                                <i class="fas fa-id-card fa-fw ob-nav-item-icon"></i> {{ __('nav.my_rights') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil" href="{{ route('account.auth') }}">
                                <i class="fas fa-shield-alt fa-fw ob-nav-item-icon"></i> {{ __('nav.authentication') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil" {{-- TODO: Migrate code — preferences.php has
                                no native route yet --}} href="{{ url('/legacy/preferences.php') }}">
                                <i class="fas fa-sliders-h fa-fw ob-nav-item-icon"></i> {{ __('nav.my_preferences') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-profil" {{-- TODO: Migrate code — upd_section.php has
                                no native route yet --}}
                                href="{{ url('/legacy/upd_section.php?S_ID=' . (auth()->user()->P_SECTION ?? 0)) }}">
                                <i class="fas fa-building fa-fw ob-nav-item-icon"></i> {{ __('nav.my_section') }}
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item dropdown-item-profil">
                                    <i class="fa fa-power-off fa-fw ob-nav-item-icon" style="color:red;"></i>
                                    {{ __('nav.logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</div>