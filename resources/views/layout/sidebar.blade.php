@php
    $leftGroups = $legacyLeftGroups ?? [];
@endphp

<div class="col-1 col-lateral noprint">
    <nav class="navbar navbar-expand-lg navbar-lateral" style="width:220px; overflow:hidden">
        <div class="div-scroll">
            <ul class="nav flex-column nav-lateral collapse navbar-collapse noprint" id="navLateral">
                <a class="navbar-brand nav-logo logo-lateral" href="{{ url('/index_d.php') }}" title="Accueil">
                    <img style="margin-right:5px;" height="40" width="40" src="{{ asset('images/logov3.png') }}"
                        onerror="this.style.display='none'">
                    {{ config('app.name') }}
                </a>

                @foreach ($leftGroups as $groupCode => $group)
                    @if (count($group['items']) > 0)
                        <li class="nav-item item-lateral mouseMenu">
                            <a class="nav-link dropdown-lateral" href="#menu-{{ $groupCode }}" data-bs-toggle="collapse"
                                aria-expanded="false">
                                @if ($group['icon'] !== '')
                                    <i class="fas fa-{{ $group['icon'] }} icon-lateral"></i>
                                @endif
                                <span>{{ $group['name'] }}</span>
                            </a>
                            <div class="collapse div-lateral" id="menu-{{ $groupCode }}">
                                @foreach ($group['items'] as $item)
                                    <a class="nav-link link-lateral"
                                        href="{{ $item['external'] ? $item['url'] : url($item['url']) }}" @if ($item['external'])
                                        target="_blank" @endif>
                                        {{ $item['name'] }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        <div class="collapse-menu"><i class="fas fa-angle-double-left"></i> Réduire le menu</div>
        <div class="decollapse-menu" style="display:none;"><i class="fas fa-angle-double-right icon-collapse"></i></div>
    </nav>
</div>