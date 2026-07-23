@extends('layout.app')

@section('title', __('admin.plugins.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.plugins.title')],
]"/>

<div class="mx-3 mt-3">

    {{-- Plugin load failures --}}
    @if (! empty($loadFailures))
        <div class="alert alert-danger">
            <i class="fas fa-triangle-exclamation me-1"></i>
            <strong>{{ __('admin.plugins.load_failures') }}</strong>
            <ul class="mb-0">
                @foreach ($loadFailures as $slug => $reason)
                    <li><code>{{ $slug }}</code> — {{ $reason }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Registry fetch errors --}}
    @foreach ($registryErrors as $registryName => $reason)
        <div class="alert alert-warning py-2" style="font-size:var(--font-size-sm);">
            <i class="fas fa-plug-circle-xmark me-1"></i>
            {{ __('admin.plugins.registry_error', ['name' => $registryName]) }} — {{ $reason }}
        </div>
    @endforeach

    {{-- ── Tabs: catalogue / dépôts ───────────────────────────────────────── --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'catalog' ? 'active' : '' }}"
               href="{{ route('admin.plugins') }}">
                <i class="fas fa-puzzle-piece me-1"></i> {{ __('admin.plugins.tab_catalog') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'registries' ? 'active' : '' }}"
               href="{{ route('admin.plugins', ['tab' => 'registries']) }}">
                <i class="fas fa-box-open me-1"></i> {{ __('admin.plugins.tab_registries') }}
                <span class="badge bg-secondary ms-1">{{ $registries->count() }}</span>
            </a>
        </li>
    </ul>

    @if ($tab === 'catalog')
    {{-- ── Catalog (store-style: search + categories + icon cards) ────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header d-flex justify-content-between align-items-center">
            <div class="ob-widget-card-title"><i class="fas fa-puzzle-piece me-2"></i>{{ __('admin.plugins.catalog_title') }}</div>
            <a href="{{ route('admin.plugins', ['refresh' => 1]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-rotate me-1"></i> {{ __('admin.plugins.refresh') }}
            </a>
        </div>

        @if ($plugins->total() === 0 && $q === '' && $category === '')
            <div class="text-center p-5">
                <i class="fas fa-puzzle-piece mb-3" style="font-size:3rem;color:var(--text-muted-soft);"></i>
                <p class="text-muted mx-auto" style="max-width:520px;font-size:var(--font-size-sm);">
                    {{ __('admin.plugins.empty_catalog') }}
                </p>
            </div>
        @else
            <div class="d-flex flex-wrap align-items-center gap-2 px-3 pt-3">
                <form method="GET" action="{{ route('admin.plugins') }}" class="position-relative">
                    @if ($category !== '')
                        <input type="hidden" name="category" value="{{ $category }}">
                    @endif
                    <i class="fas fa-magnifying-glass position-absolute" style="left:10px;top:9px;color:var(--text-muted-soft);font-size:var(--font-size-xs);"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm ps-4" style="min-width:240px;"
                           placeholder="{{ __('admin.plugins.search_ph') }}">
                </form>
                <div class="d-flex flex-wrap gap-1">
                    <a href="{{ route('admin.plugins', array_filter(['q' => $q])) }}"
                       class="btn btn-sm {{ $category === '' ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ __('admin.plugins.cat_all') }}</a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('admin.plugins', array_filter(['q' => $q, 'category' => $cat])) }}"
                           class="btn btn-sm {{ $category === $cat ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $cat }}</a>
                    @endforeach
                </div>
            </div>

            @if ($plugins->total() === 0)
                <div class="text-center text-muted p-5" style="font-size:var(--font-size-sm);">
                    {{ __('admin.plugins.no_results') }}
                </div>
            @endif

            <div class="row g-3 p-3">
                @foreach ($plugins as $plugin)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    @if (! empty($plugin['icon']))
                                        <img src="{{ $plugin['icon'] }}" alt="" width="48" height="48"
                                             class="rounded flex-shrink-0" style="object-fit:cover;"
                                             onerror="this.outerHTML='<span class=&quot;ob-plugin-icon-fallback&quot;><i class=&quot;fas fa-puzzle-piece&quot;></i></span>'">
                                    @else
                                        <span class="ob-plugin-icon-fallback">
                                            <i class="fas fa-puzzle-piece"></i>
                                        </span>
                                    @endif
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex justify-content-between align-items-start gap-1">
                                            <h6 class="mb-0">{{ $plugin['name'] }}</h6>
                                            <div class="text-nowrap">
                                                @if ($plugin['enabled'])
                                                    <span class="ob-badge ob-badge-int">{{ __('admin.plugins.state_enabled') }}</span>
                                                @elseif ($plugin['installed'])
                                                    <span class="ob-badge ob-badge-archive">{{ __('admin.plugins.state_disabled') }}</span>
                                                @endif
                                                @if ($plugin['update_available'])
                                                    <span class="ob-badge ob-badge-ben">{{ __('admin.plugins.state_update', ['version' => $plugin['version']]) }}</span>
                                                @endif
                                                @if (! ($plugin['compatible'] ?? true) || ($plugin['installed_incompatible'] ?? false))
                                                    <span class="ob-badge ob-badge-bloqued"
                                                          title="{{ __('admin.plugins.requires_range', ['range' => ($plugin['min_app_version'] ?? '?').(($plugin['max_app_version'] ?? '') !== '' ? ' – '.$plugin['max_app_version'] : ' +')]) }}">
                                                        {{ __('admin.plugins.state_incompatible') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                                            @if (! empty($plugin['author'])){{ $plugin['author'] }} · @endif
                                            v{{ $plugin['installed_version'] ?? $plugin['version'] }}
                                            @if (! empty($plugin['category'])) · <span class="ob-badge ob-badge-ext">{{ $plugin['category'] }}</span> @endif
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-0 mt-2 ob-plugin-desc" style="font-size:var(--font-size-sm);">{{ $plugin['description'] }}</p>
                            </div>
                            <div class="card-footer d-flex gap-2 py-2">
                                @if (! empty($plugin['screenshots']) || ! empty($plugin['homepage']) || ! empty($plugin['registry']))
                                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                                            data-bs-target="#pluginModal-{{ $plugin['slug'] }}">
                                        <i class="fas fa-circle-info me-1"></i> {{ __('admin.plugins.btn_details') }}
                                    </button>
                                @endif
                                @if (! $plugin['installed'])
                                    @if ($plugin['compatible'] ?? true)
                                        <form method="POST" action="{{ route('admin.plugins.install', $plugin['slug']) }}"
                                              onsubmit="return confirm('{{ __('admin.plugins.confirm_install') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-download me-1"></i> {{ __('admin.plugins.btn_install') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted align-self-center" style="font-size:var(--font-size-xs);">
                                            {{ __('admin.plugins.requires_range', ['range' => ($plugin['min_app_version'] ?? '?').(($plugin['max_app_version'] ?? '') !== '' ? ' – '.$plugin['max_app_version'] : ' +')]) }}
                                        </span>
                                    @endif
                                @else
                                    @if ($plugin['update_available'])
                                        <form method="POST" action="{{ route('admin.plugins.install', $plugin['slug']) }}"
                                              onsubmit="return confirm('{{ __('admin.plugins.confirm_update') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-arrow-up me-1"></i> {{ __('admin.plugins.btn_update') }}
                                            </button>
                                        </form>
                                    @endif
                                    @if ($plugin['enabled'])
                                        <form method="POST" action="{{ route('admin.plugins.disable', $plugin['slug']) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-toggle-off me-1"></i> {{ __('admin.plugins.btn_disable') }}
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.plugins.enable', $plugin['slug']) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-toggle-on me-1"></i> {{ __('admin.plugins.btn_enable') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.plugins.uninstall', $plugin['slug']) }}"
                                              onsubmit="return confirm('{{ __('admin.plugins.confirm_uninstall') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash me-1"></i> {{ __('admin.plugins.btn_uninstall') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                @if (! empty($plugin['registry']))
                                    {{-- Source registry — one colour per dépôt so origins read at a glance --}}
                                    <span class="ob-badge ob-plugin-registry-badge ms-auto align-self-center"
                                          style="background:hsl({{ crc32($plugin['registry']) % 360 }}, 45%, 38%);">
                                        <i class="fas fa-box-open me-1"></i>{{ $plugin['registry'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Store-style detail sheet --}}
                        <div class="modal fade" id="pluginModal-{{ $plugin['slug'] }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div class="d-flex align-items-center gap-2">
                                            @if (! empty($plugin['icon']))
                                                <img src="{{ $plugin['icon'] }}" alt="" width="32" height="32" class="rounded" style="object-fit:cover;">
                                            @endif
                                            <h5 class="modal-title" style="font-size:var(--font-size-base);">
                                                {{ $plugin['name'] }}
                                                <span class="text-muted" style="font-size:var(--font-size-xs);">v{{ $plugin['version'] }}</span>
                                            </h5>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if (! empty($plugin['screenshots']))
                                            <div class="d-flex gap-2 mb-3 pb-1" style="overflow-x:auto;">
                                                @foreach ($plugin['screenshots'] as $shot)
                                                    <a href="{{ $shot }}" target="_blank" rel="noopener" class="flex-shrink-0">
                                                        <img src="{{ $shot }}" alt="" style="height:180px;border-radius:6px;border:1px solid var(--component-border);">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                        <p style="font-size:var(--font-size-sm);">{{ $plugin['description'] }}</p>
                                        <table class="table table-sm mb-0" style="font-size:var(--font-size-sm);">
                                            <tbody>
                                                @if (! empty($plugin['author']))
                                                    <tr><td class="text-muted" style="width:35%;">{{ __('admin.plugins.detail_author') }}</td><td>{{ $plugin['author'] }}</td></tr>
                                                @endif
                                                @if (! empty($plugin['category']))
                                                    <tr><td class="text-muted">{{ __('admin.plugins.detail_category') }}</td><td>{{ $plugin['category'] }}</td></tr>
                                                @endif
                                                @if (! empty($plugin['registry']))
                                                    <tr><td class="text-muted">{{ __('admin.plugins.detail_registry') }}</td><td>{{ $plugin['registry'] }}</td></tr>
                                                @endif
                                                @if (! empty($plugin['min_app_version']))
                                                    <tr><td class="text-muted">{{ __('admin.plugins.detail_compat') }}</td>
                                                        <td>{{ __('admin.plugins.requires_range', ['range' => $plugin['min_app_version'].(($plugin['max_app_version'] ?? '') !== '' ? ' – '.$plugin['max_app_version'] : ' +')]) }}</td></tr>
                                                @endif
                                                @if (! empty($plugin['homepage']))
                                                    <tr><td class="text-muted">{{ __('admin.plugins.detail_homepage') }}</td>
                                                        <td><a href="{{ $plugin['homepage'] }}" target="_blank" rel="noopener">{{ $plugin['homepage'] }}</a></td></tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($plugins->hasPages())
                {{-- Same footer treatment as the Journal d'activité tables --}}
                <div class="ob-plugins-pagination d-flex justify-content-between align-items-center px-3 pb-3">
                    <span class="text-muted" style="font-size:var(--font-size-xs);">
                        {{ trans_choice('admin.plugins.count', $plugins->total(), ['count' => $plugins->total()]) }}
                    </span>
                    {{ $plugins->onEachSide(1)->links() }}
                </div>
            @endif
        @endif

        <div class="text-muted px-3 pb-3" style="font-size:var(--font-size-xs);">
            <i class="fas fa-shield-halved me-1"></i> {{ __('admin.plugins.security_note') }}
        </div>
    </div>
    @endif

    @if ($tab === 'registries')
    {{-- ── Registries ─────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-box-open me-2"></i>{{ __('admin.plugins.registries_title') }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">{{ __('admin.plugins.col_registry') }}</th>
                        <th>{{ __('admin.plugins.col_url') }}</th>
                        <th style="width:180px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registries as $registry)
                        <tr>
                            <td class="ps-3" style="font-size:var(--font-size-sm);">
                                <span class="ob-plugin-registry-dot me-1"
                                      style="background:hsl({{ crc32($registry->name) % 360 }}, 45%, 38%);"></span>
                                {{ $registry->name }}
                                @if ($registry->is_default)
                                    <span class="ob-badge ob-badge-int ms-1">{{ __('admin.plugins.registry_official') }}</span>
                                @endif
                                @unless ($registry->enabled)
                                    <span class="ob-badge ob-badge-archive ms-1">{{ __('admin.plugins.registry_disabled') }}</span>
                                @endunless
                                @unless ($registry->verify_ssl)
                                    <span class="ob-badge ob-badge-bloqued ms-1">{{ __('admin.plugins.badge_no_ssl') }}</span>
                                @endunless
                                @unless ($registry->verify_checksum)
                                    <span class="ob-badge ob-badge-bloqued ms-1">{{ __('admin.plugins.badge_no_checksum') }}</span>
                                @endunless
                            </td>
                            <td class="text-muted" style="font-size:var(--font-size-xs);word-break:break-all;">{{ $registry->url }}</td>
                            <td class="text-end pe-3">
                                <form method="POST" action="{{ route('admin.plugins.registries.toggle', $registry) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-light py-0 px-1" title="{{ $registry->enabled ? __('admin.plugins.registry_toggle_off') : __('admin.plugins.registry_toggle_on') }}">
                                        <i class="fas {{ $registry->enabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.plugins.registries.toggle', $registry) }}" class="d-inline"
                                      @if ($registry->verify_ssl) onsubmit="return confirm('{{ __('admin.plugins.confirm_skip_ssl') }}')" @endif>
                                    @csrf
                                    <input type="hidden" name="field" value="verify_ssl">
                                    <button type="submit" class="btn btn-xs btn-light py-0 px-1 {{ $registry->verify_ssl ? '' : 'text-danger' }}"
                                            title="{{ __('admin.plugins.toggle_ssl') }}">
                                        <i class="fas fa-lock{{ $registry->verify_ssl ? '' : '-open' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.plugins.registries.toggle', $registry) }}" class="d-inline"
                                      @if ($registry->verify_checksum) onsubmit="return confirm('{{ __('admin.plugins.confirm_skip_checksum') }}')" @endif>
                                    @csrf
                                    <input type="hidden" name="field" value="verify_checksum">
                                    <button type="submit" class="btn btn-xs btn-light py-0 px-1 {{ $registry->verify_checksum ? '' : 'text-danger' }}"
                                            title="{{ __('admin.plugins.toggle_checksum') }}">
                                        <i class="fas fa-file-shield"></i>
                                    </button>
                                </form>
                                @unless ($registry->is_default)
                                    <form method="POST" action="{{ route('admin.plugins.registries.destroy', $registry) }}" class="d-inline"
                                          onsubmit="return confirm('{{ __('admin.plugins.confirm_registry_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ route('admin.plugins.registries.store') }}" class="d-flex flex-wrap gap-2 p-3">
            @csrf
            <input type="text" name="name" class="form-control form-control-sm" style="max-width:220px;"
                   placeholder="{{ __('admin.plugins.registry_name_ph') }}" required maxlength="100">
            <input type="url" name="url" class="form-control form-control-sm" style="max-width:420px;"
                   placeholder="{{ __('admin.plugins.registry_url_ph') }}" required maxlength="500">
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> {{ __('admin.plugins.btn_add_registry') }}
            </button>
        </form>
        <div class="text-muted px-3 pb-3" style="font-size:var(--font-size-xs);">
            {{ __('admin.plugins.registries_hint') }}
        </div>
    </div>
    @endif
</div>

@endsection
