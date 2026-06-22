{{-- Per-person ACL overrides. Vars: $featuresByCategory, $sections, $scopeId,
     $people, $person, $userGrants (feature_id => 'allow'|'deny'), $obsolete --}}
@php $obsolete = $obsolete ?? []; @endphp
<div class="ob-hab-matrix" data-hab-matrix>

    <div class="ob-hab-toolbar">
        <span class="fw-semibold"><i class="fas fa-user-shield me-1 text-secondary"></i>{{ __('admin.permissions.overrides_title') }}</span>
        <span class="text-muted" style="font-size:var(--font-size-xs);">
            {!! __('admin.permissions.overrides_hint') !!}
        </span>
    </div>

    {{-- Person search + scope --}}
    <form method="GET" action="{{ route('admin.permissions') }}" class="ob-hab-create mb-2">
        <input type="hidden" name="tab" value="overrides">
        @if ($person)<input type="hidden" name="person" value="{{ $person->P_ID }}">@endif
        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.permissions.search_person_ph') }}"
               class="form-control form-control-sm" style="width:240px;">
        @feature('multi_site')
        <select name="section" class="form-select form-select-sm ob-hab-auto" style="width:auto;" aria-label="{{ __('admin.permissions.scope_aria') }}">
            <option value="-1" {{ (int) $scopeId === -1 ? 'selected' : '' }}>{{ __('admin.permissions.all_sections_scope') }}</option>
            @foreach ($sections as $s)
                <option value="{{ $s->S_ID }}" {{ (int) $s->S_ID === (int) $scopeId ? 'selected' : '' }}>{{ $s->S_DESCRIPTION }}</option>
            @endforeach
        </select>
        @endfeature
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>{{ __('common.search') }}</button>
    </form>

    {{-- Search results --}}
    @if ($people->isNotEmpty())
        <div class="ob-hab-people mb-3">
            @foreach ($people as $p)
                <a href="{{ route('admin.permissions', ['tab' => 'overrides', 'person' => $p->P_ID, 'section' => $scopeId]) }}"
                   class="ob-hab-person {{ $person && (int) $person->P_ID === (int) $p->P_ID ? 'active' : '' }}">
                    <i class="fas fa-user fa-fw me-1"></i>{{ $p->P_NOM }} {{ $p->P_PRENOM }}
                </a>
            @endforeach
        </div>
    @elseif (request('q'))
        <div class="text-muted mb-3" style="font-size:var(--font-size-sm);">{{ __('admin.permissions.no_person') }}</div>
    @endif

    @if (! $person)
        <div class="ob-widget-empty">{{ __('admin.permissions.select_person') }}</div>
    @else
        @php
            $scopeLabel = (int) $scopeId === -1
                ? __('admin.permissions.all_sections')
                : ($sections->firstWhere('S_ID', $scopeId)->S_DESCRIPTION ?? __('admin.permissions.section_n', ['id' => $scopeId]));
        @endphp
        <p class="text-muted mb-2" style="font-size:var(--font-size-xs);">
            <i class="fas fa-circle-info me-1"></i>
            {!! __('admin.permissions.overrides_scope', ['name' => e($person->P_NOM.' '.$person->P_PRENOM), 'scope' => e($scopeLabel)]) !!}
        </p>

        <div class="ob-hab-matrix-scroll">
            <table class="ob-hab-table">
                <thead>
                    <tr>
                        <th class="ob-hab-feat-head">{{ __('admin.permissions.feature_col') }}</th>
                        <th class="ob-hab-colhead" style="min-width:130px;writing-mode:horizontal-tb;transform:none;">{{ __('admin.permissions.override_col') }}</th>
                    </tr>
                </thead>
                @foreach ($featuresByCategory as $category => $features)
                    <tbody data-hab-cat>
                        <tr class="ob-hab-cat-row">
                            <td colspan="2">
                                <i class="fas fa-chevron-down ob-hab-chevron me-1"></i>{{ $category ?: __('admin.general') }}
                                <span class="text-muted ms-1" style="font-weight:400;text-transform:none;">({{ $features->count() }})</span>
                            </td>
                        </tr>
                        @foreach ($features as $f)
                            @php $isObsolete = in_array((int) $f->F_ID, $obsolete, true); @endphp
                            <tr class="ob-hab-feat">
                                <td class="ob-hab-feat-cell" title="{{ $f->F_DESCRIPTION }}">
                                    {{ $f->F_LIBELLE }}
                                    <span class="text-muted ms-1" style="font-size:10px;">#{{ $f->F_ID }}</span>
                                    @if ($f->F_FLAG)<span class="ob-badge ob-badge-bloqued ms-1" style="font-size:9px;">{{ __('admin.permissions.badge_sensitive') }}</span>@endif
                                    @if ($isObsolete)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;" title="{{ __('admin.permissions.obsolete_title') }}">{{ __('admin.permissions.badge_obsolete') }}</span>@endif
                                </td>
                                <td class="ob-hab-cell">
                                    @include('admin.permissions.partials.effect-cell', [
                                        'action'  => route('admin.permissions.user.set'),
                                        'hidden'  => ['person_id' => $person->P_ID, 'feature_id' => $f->F_ID, 'section_id' => $scopeId],
                                        'current' => $userGrants[(int) $f->F_ID] ?? null,
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>
    @endif
</div>
