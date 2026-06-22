{{-- Group/role grant matrix. Vars: $featuresByCategory, $columns, $grants (key
     "group|feature" => 'allow'|'deny'), $sectionDenied, $sections, $sectionId,
     $tab, $kind ('group'|'role'), $title, $hint, $obsolete --}}
@php $obsolete = $obsolete ?? []; @endphp
<div class="ob-hab-matrix" data-hab-matrix>

    {{-- Toolbar: title + section preview + create form on top --}}
    <div class="ob-hab-toolbar">
        <span class="fw-semibold"><i class="fas fa-{{ $kind === 'role' ? 'user-tie' : 'key' }} me-1 text-secondary"></i>{{ $title }}</span>
        <span class="text-muted" style="font-size:var(--font-size-xs);">{{ $hint }}</span>

        @feature('multi_site')
        <span class="ms-auto d-flex align-items-center gap-2">
            <span class="text-muted" style="font-size:var(--font-size-xs);">{{ $kind === 'role' ? __('admin.permissions.section_label') : __('admin.permissions.ceiling_preview') }}</span>
            <form method="GET" action="{{ route('admin.permissions') }}" style="margin:0;">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="section" class="form-select form-select-sm ob-hab-auto" style="width:auto;">
                    @foreach ($sections as $s)
                        <option value="{{ $s->S_ID }}" {{ (int) $s->S_ID === (int) $sectionId ? 'selected' : '' }}>{{ $s->S_DESCRIPTION }}</option>
                    @endforeach
                </select>
            </form>
        </span>
        @endfeature
    </div>

    {{-- Create a new group/role (top of page) --}}
    <form method="POST" action="{{ route('admin.permissions.group.store') }}" class="ob-hab-create mb-2">
        @csrf
        <input type="hidden" name="kind" value="{{ $kind }}">
        <input type="text" name="name" placeholder="{{ __('admin.permissions.new_group_ph', ['kind' => $kind === 'role' ? __('admin.permissions.kind_role') : __('admin.permissions.kind_group')]) }}"
               class="form-control form-control-sm" style="width:200px;" maxlength="60" required>
        <select name="usage" class="form-select form-select-sm" style="width:120px;">
            <option value="internes">{{ __('admin.permissions.usage_internal') }}</option>
            <option value="externes">{{ __('admin.permissions.usage_external') }}</option>
            <option value="all">{{ __('admin.permissions.usage_all') }}</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>{{ __('common.add') }}</button>
    </form>
    @if ($errors->any())
        <div class="text-danger mb-2" style="font-size:var(--font-size-xs);">{{ $errors->first() }}</div>
    @endif

    @if ($columns->isEmpty())
        <div class="text-muted mb-2" style="font-size:var(--font-size-sm);">{{ __('admin.permissions.no_column', ['kind' => $kind === 'role' ? __('admin.permissions.kind_role') : __('admin.permissions.kind_group')]) }}</div>
    @else
        <div class="ob-hab-legend">
            <span><i class="fas fa-check text-success"></i> {{ __('admin.permissions.legend_allow') }}</span>
            <span><i class="fas fa-ban text-danger"></i> {{ __('admin.permissions.legend_deny') }}</span>
            <span><span class="text-muted">·</span> {{ __('admin.permissions.legend_neutral') }}</span>
        </div>

        {{-- Column show/hide pills --}}
        <div class="ob-hab-pills">
            <span class="ob-hab-pills-label">{{ $kind === 'role' ? __('admin.permissions.kind_roles') : __('admin.permissions.kind_groups') }} :</span>
            <button type="button" class="ob-hab-pill ob-hab-pill-all active" data-col="all">{{ __('admin.permissions.usage_all') }}</button>
            @foreach ($columns as $c)
                <button type="button" class="ob-hab-pill active" data-col="{{ $c->id }}">{{ $c->name }}</button>
            @endforeach
        </div>

        <div class="ob-hab-matrix-scroll">
            <table class="ob-hab-table">
                <thead>
                    <tr>
                        <th class="ob-hab-feat-head">{{ __('admin.permissions.feature_col') }}</th>
                        @foreach ($columns as $c)
                            <th class="ob-hab-colhead" data-col="{{ $c->id }}">
                                {{ $c->name }} <span style="opacity:.6;">({{ $c->id }})</span>
                                @if ($c->is_system)<span class="ob-badge ob-badge-archive ms-1" style="font-size:8px;">{{ __('admin.permissions.badge_sys') }}</span>@endif
                                <a href="{{ route('admin.permissions.group.export', ['gpId' => $c->id, 'format' => 'xlsx']) }}"
                                   class="ms-1" title="{{ __('admin.permissions.export_xls_title') }}" style="font-size:11px; text-decoration:none;">
                                    <i class="fas fa-file-excel text-success"></i>
                                </a>
                                <a href="{{ route('admin.permissions.group.export', ['gpId' => $c->id, 'format' => 'csv']) }}"
                                   title="{{ __('admin.permissions.export_csv_title') }}" style="font-size:11px; text-decoration:none;">
                                    <i class="fas fa-file-csv text-secondary"></i>
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                @foreach ($featuresByCategory as $category => $features)
                    <tbody data-hab-cat>
                        <tr class="ob-hab-cat-row">
                            <td colspan="{{ $columns->count() + 1 }}">
                                <i class="fas fa-chevron-down ob-hab-chevron me-1"></i>{{ $category ?: __('admin.general') }}
                                <span class="text-muted ms-1" style="font-weight:400;text-transform:none;">({{ $features->count() }})</span>
                            </td>
                        </tr>
                        @foreach ($features as $f)
                            @php
                                $capped     = in_array((int) $f->F_ID, $sectionDenied, true);
                                $isObsolete = in_array((int) $f->F_ID, $obsolete, true);
                            @endphp
                            <tr class="ob-hab-feat {{ $capped ? 'ob-hab-row-capped' : '' }}">
                                <td class="ob-hab-feat-cell" title="{{ $f->F_DESCRIPTION }}">
                                    {{ $f->F_LIBELLE }}
                                    <span class="text-muted ms-1" style="font-size:10px;">#{{ $f->F_ID }}</span>
                                    @if ($f->F_FLAG)<span class="ob-badge ob-badge-bloqued ms-1" style="font-size:9px;">{{ __('admin.permissions.badge_sensitive') }}</span>@endif
                                    @if ($isObsolete)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;" title="{{ __('admin.permissions.obsolete_title') }}">{{ __('admin.permissions.badge_obsolete') }}</span>@endif
                                    @if ($capped)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;" title="{{ __('admin.permissions.locked_title') }}">{{ __('admin.permissions.badge_capped') }}</span>@endif
                                </td>
                                @foreach ($columns as $c)
                                    @php $effect = $grants->get("{$c->id}|{$f->F_ID}"); @endphp
                                    <td class="ob-hab-cell" data-col="{{ $c->id }}">
                                        @if ($kind === 'role' && $capped)
                                            <i class="fas fa-lock text-muted" title="{{ __('admin.permissions.locked_title') }}"></i>
                                        @else
                                            @include('admin.permissions.partials.effect-cell', [
                                                'action'  => route('admin.permissions.grant.set'),
                                                'hidden'  => ['group_id' => $c->id, 'feature_id' => $f->F_ID, 'tab' => $tab, 'section' => $sectionId],
                                                'current' => $effect,
                                            ])
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>
    @endif
</div>
