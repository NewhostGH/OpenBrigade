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
            <span class="text-muted" style="font-size:var(--font-size-xs);">{{ $kind === 'role' ? 'Section :' : 'Aperçu du plafond :' }}</span>
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
        <input type="text" name="name" placeholder="Nouveau {{ $kind === 'role' ? 'rôle' : 'groupe' }}…"
               class="form-control form-control-sm" style="width:200px;" maxlength="60" required>
        <select name="usage" class="form-select form-select-sm" style="width:120px;">
            <option value="internes">Internes</option>
            <option value="externes">Externes</option>
            <option value="all">Tous</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Ajouter</button>
    </form>
    @if ($errors->any())
        <div class="text-danger mb-2" style="font-size:var(--font-size-xs);">{{ $errors->first() }}</div>
    @endif

    @if ($columns->isEmpty())
        <div class="text-muted mb-2" style="font-size:var(--font-size-sm);">Aucun {{ $kind === 'role' ? 'rôle' : 'groupe' }} défini.</div>
    @else
        <div class="ob-hab-legend">
            <span><i class="fas fa-check text-success"></i> autorise</span>
            <span><i class="fas fa-ban text-danger"></i> refuse (prioritaire sur les autres groupes)</span>
            <span><span class="text-muted">·</span> neutre</span>
        </div>

        {{-- Column show/hide pills --}}
        <div class="ob-hab-pills">
            <span class="ob-hab-pills-label">{{ $kind === 'role' ? 'Rôles' : 'Groupes' }} :</span>
            <button type="button" class="ob-hab-pill ob-hab-pill-all active" data-col="all">Tous</button>
            @foreach ($columns as $c)
                <button type="button" class="ob-hab-pill active" data-col="{{ $c->id }}">{{ $c->name }}</button>
            @endforeach
        </div>

        <div class="ob-hab-matrix-scroll">
            <table class="ob-hab-table">
                <thead>
                    <tr>
                        <th class="ob-hab-feat-head">Fonctionnalité</th>
                        @foreach ($columns as $c)
                            <th class="ob-hab-colhead" data-col="{{ $c->id }}">
                                {{ $c->name }} <span style="opacity:.6;">({{ $c->id }})</span>
                                @if ($c->is_system)<span class="ob-badge ob-badge-archive ms-1" style="font-size:8px;">sys</span>@endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                @foreach ($featuresByCategory as $category => $features)
                    <tbody data-hab-cat>
                        <tr class="ob-hab-cat-row">
                            <td colspan="{{ $columns->count() + 1 }}">
                                <i class="fas fa-chevron-down ob-hab-chevron me-1"></i>{{ $category ?: 'Général' }}
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
                                    @if ($f->F_FLAG)<span class="ob-badge ob-badge-bloqued ms-1" style="font-size:9px;">sensible</span>@endif
                                    @if ($isObsolete)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;" title="Fonctionnalité qui ne sera pas portée">obsolète</span>@endif
                                    @if ($capped)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;" title="Refusé par la section affichée">plafonné</span>@endif
                                </td>
                                @foreach ($columns as $c)
                                    @php $effect = $grants->get("{$c->id}|{$f->F_ID}"); @endphp
                                    <td class="ob-hab-cell" data-col="{{ $c->id }}">
                                        @if ($c->is_system)
                                            @if ($effect === 'allow')<i class="fas fa-check text-success"></i>
                                            @elseif ($effect === 'deny')<i class="fas fa-ban text-danger"></i>
                                            @else<i class="fas fa-minus text-muted" style="font-size:10px;"></i>@endif
                                        @elseif ($kind === 'role' && $capped)
                                            <i class="fas fa-lock text-muted" title="Refusé par la section affichée"></i>
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
