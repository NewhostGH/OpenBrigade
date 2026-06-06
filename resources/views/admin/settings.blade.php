@extends('layout.app')

@section('title', 'Configuration — ' . config('app.name'))

@push('scripts')
<script>
document.querySelectorAll('.ob-setting-toggle').forEach(function (el) {
    el.addEventListener('change', function () {
        this.closest('form').submit();
    });
});
</script>
@endpush

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Configuration'],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-cog me-2"></i>Configuration de l'application</div>
        </div>

        {{-- Tab nav --}}
        <ul class="nav nav-tabs px-3 pt-2" role="tablist">
            @foreach($tabs as $tabId => $tab)
                @if($grouped->has($tabId))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($loop->first) active @endif"
                                id="tab-{{ $tabId }}-btn"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-{{ $tabId }}"
                                type="button" role="tab">
                            <i class="fas fa-{{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
                        </button>
                    </li>
                @endif
            @endforeach
        </ul>

        <div class="tab-content p-3">
            @foreach($tabs as $tabId => $tab)
                @if($grouped->has($tabId))
                    <div class="tab-pane fade @if($loop->first) show active @endif"
                         id="tab-{{ $tabId }}" role="tabpanel">

                        @if($tabId === 6)
                            {{-- Module cards --}}
                            <div class="d-flex flex-wrap gap-3">
                            @foreach($grouped->get($tabId) as $row)
                                @if($row->CARD_NAME)
                                    <div class="ob-widget-card p-3" style="min-width:180px;flex:0 0 auto;">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <form method="POST"
                                                  action="{{ route('admin.settings.save', $row->ID) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="toggle" value="1">
                                                <input type="hidden" name="VALUE" value="{{ $row->VALUE == '1' ? '0' : '1' }}">
                                                <button type="submit"
                                                        class="btn btn-sm {{ $row->VALUE == '1' ? 'btn-success' : 'btn-outline-secondary' }}"
                                                        title="{{ $row->VALUE == '1' ? 'Désactiver' : 'Activer' }}">
                                                    <i class="fas fa-{{ $row->VALUE == '1' ? 'check' : 'times' }}"></i>
                                                </button>
                                            </form>
                                            <span class="fw-semibold" style="font-size:var(--font-size-sm);">
                                                {{ $row->CARD_NAME }}
                                            </span>
                                        </div>
                                        @if($row->DESCRIPTION)
                                            <div class="text-muted" style="font-size:var(--font-size-xs);">
                                                {{ $row->DESCRIPTION }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                            </div>
                        @else
                            <table class="table table-sm table-hover mb-0">
                                <tbody>
                                @foreach($grouped->get($tabId) as $row)
                                    @php
                                        $label = $row->DISPLAY_NAME ?: $row->NAME;
                                        $label = strip_tags($label);
                                    @endphp
                                    <tr>
                                        <td style="width:55%;vertical-align:middle;font-size:var(--font-size-sm);">
                                            <span title="{{ $row->DESCRIPTION ?? '' }}">{{ $label }}</span>
                                            @if($row->DESCRIPTION)
                                                <div class="text-muted" style="font-size:var(--font-size-xs);">
                                                    {{ Str::limit($row->DESCRIPTION, 120) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="vertical-align:middle;">
                                            @if($row->ID == 1)
                                                {{-- Version: read-only --}}
                                                <span class="ob-badge ob-badge-int">{{ $row->VALUE }}</span>

                                            @elseif($row->IS_FILE)
                                                {{-- File setting: show current, text-edit --}}
                                                <form method="POST" action="{{ route('admin.settings.save', $row->ID) }}"
                                                      class="d-flex gap-2 align-items-center">
                                                    @csrf @method('PATCH')
                                                    <input type="text" name="VALUE"
                                                           value="{{ $row->VALUE }}"
                                                           class="form-control form-control-sm"
                                                           style="max-width:280px;"
                                                           placeholder="Chemin du fichier">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>

                                            @elseif($row->YESNO)
                                                {{-- Toggle --}}
                                                <form method="POST" action="{{ route('admin.settings.save', $row->ID) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="toggle" value="1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input ob-setting-toggle"
                                                               type="checkbox"
                                                               name="VALUE"
                                                               value="1"
                                                               {{ $row->VALUE == '1' ? 'checked' : '' }}>
                                                    </div>
                                                </form>

                                            @elseif($row->ID == 54)
                                                {{-- Error reporting: special select --}}
                                                <form method="POST" action="{{ route('admin.settings.save', $row->ID) }}"
                                                      class="d-flex gap-2 align-items-center">
                                                    @csrf @method('PATCH')
                                                    <select name="VALUE" class="form-select form-select-sm" style="max-width:220px;">
                                                        <option value="0" @selected($row->VALUE=='0')>Aucune</option>
                                                        <option value="1" @selected($row->VALUE=='1')>Erreurs seulement</option>
                                                        <option value="2" @selected($row->VALUE=='2')>Erreurs + Warnings</option>
                                                        <option value="3" @selected($row->VALUE=='3')>Tout afficher</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>

                                            @elseif($row->ID == 44)
                                                {{-- Encryption method --}}
                                                <form method="POST" action="{{ route('admin.settings.save', $row->ID) }}"
                                                      class="d-flex gap-2 align-items-center">
                                                    @csrf @method('PATCH')
                                                    <select name="VALUE" class="form-select form-select-sm" style="max-width:160px;">
                                                        <option value="md5"    @selected($row->VALUE=='md5')>MD5</option>
                                                        <option value="pbkdf2" @selected($row->VALUE=='pbkdf2')>PBKDF2</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>

                                            @elseif($row->ID == 15)
                                                {{-- Password quality level --}}
                                                <form method="POST" action="{{ route('admin.settings.save', $row->ID) }}"
                                                      class="d-flex gap-2 align-items-center">
                                                    @csrf @method('PATCH')
                                                    <select name="VALUE" class="form-select form-select-sm" style="max-width:200px;">
                                                        <option value="0" @selected($row->VALUE=='0')>Aucune restriction</option>
                                                        <option value="1" @selected($row->VALUE=='1')>Minimum (longueur)</option>
                                                        <option value="2" @selected($row->VALUE=='2')>Fort (complexité)</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>

                                            @else
                                                {{-- Text / number input --}}
                                                <form method="POST" action="{{ route('admin.settings.save', $row->ID) }}"
                                                      class="d-flex gap-2 align-items-center">
                                                    @csrf @method('PATCH')
                                                    <input type="text" name="VALUE"
                                                           value="{{ $row->VALUE }}"
                                                           class="form-control form-control-sm"
                                                           style="max-width:280px;">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

@endsection
