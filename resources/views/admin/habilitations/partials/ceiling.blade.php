{{-- Section deny-list editor. Vars: $sections, $sectionId, $selected, $featuresByCategory,
     $ownDenied, $parentDenied, $obsolete --}}
@php $obsolete = $obsolete ?? []; @endphp
<div class="row g-3">

    {{-- Section tree --}}
    <div class="col-12 col-md-4 col-lg-3">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-sitemap me-2"></i>Sections</div>
            </div>
            <div class="p-2">
                @foreach ($sections as $s)
                    <a href="{{ route('admin.habilitations', ['tab' => 'ceiling', 'section' => $s->S_ID]) }}"
                       class="d-block px-2 py-1 rounded text-decoration-none {{ (int) $s->S_ID === (int) $sectionId ? 'bg-primary text-white' : 'text-body' }}"
                       style="font-size:var(--font-size-sm);{{ (int) $s->S_PARENT !== 0 ? 'padding-left:1.4rem!important;' : '' }}">
                        <i class="fas fa-{{ (int) $s->S_PARENT === 0 ? 'building' : 'angle-right' }} fa-fw me-1"></i>{{ $s->S_DESCRIPTION }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Ceiling editor for the selected section --}}
    <div class="col-12 col-md-8 col-lg-9">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-layer-group me-2"></i>Plafond — {{ $selected->S_DESCRIPTION ?? '—' }}
                </div>
            </div>
            <div class="p-3">
                <p class="text-muted mb-3" style="font-size:var(--font-size-xs);">
                    Décochez une fonctionnalité pour la <strong>refuser</strong> à cette section et à toutes ses
                    sections filles. Une fonctionnalité <i class="fas fa-lock"></i> est déjà refusée par une section
                    parente : elle ne peut pas être réautorisée ici.
                </p>

                @if (! $selected)
                    <div class="text-muted">Sélectionnez une section.</div>
                @else
                    <div class="ob-hab-matrix-scroll">
                        <table class="ob-hab-table">
                            <thead>
                                <tr>
                                    <th class="ob-hab-feat-head">Fonctionnalité</th>
                                    <th class="ob-hab-colhead" style="min-width:90px;writing-mode:horizontal-tb;transform:none;">Autorisé</th>
                                </tr>
                            </thead>
                            @foreach ($featuresByCategory as $category => $features)
                                <tbody data-hab-cat>
                                    <tr class="ob-hab-cat-row">
                                        <td colspan="2">
                                            <i class="fas fa-chevron-down ob-hab-chevron me-1"></i>{{ $category ?: 'Général' }}
                                            <span class="text-muted ms-1" style="font-weight:400;text-transform:none;">({{ $features->count() }})</span>
                                        </td>
                                    </tr>
                                    @foreach ($features as $f)
                                        @php
                                            $locked      = in_array((int) $f->F_ID, $parentDenied, true);
                                            $allowedHere = ! $locked && ! in_array((int) $f->F_ID, $ownDenied, true);
                                            $isObsolete  = in_array((int) $f->F_ID, $obsolete, true);
                                        @endphp
                                        <tr class="ob-hab-feat {{ $locked ? 'ob-hab-row-capped' : '' }}">
                                            <td class="ob-hab-feat-cell" title="{{ $f->F_DESCRIPTION }}">
                                                {{ $f->F_LIBELLE }}
                                                <span class="text-muted ms-1" style="font-size:10px;">#{{ $f->F_ID }}</span>
                                                @if ($f->F_FLAG)<span class="ob-badge ob-badge-bloqued ms-1" style="font-size:9px;">sensible</span>@endif
                                                @if ($isObsolete)<span class="ob-badge ob-badge-archive ms-1" style="font-size:9px;" title="Fonctionnalité qui ne sera pas portée">obsolète</span>@endif
                                            </td>
                                            <td class="ob-hab-cell">
                                                @if ($locked)
                                                    <i class="fas fa-lock text-muted" title="Refusé par une section parente"></i>
                                                @else
                                                    <form method="POST" action="{{ route('admin.habilitations.ceiling.toggle') }}" style="margin:0;">
                                                        @csrf
                                                        <input type="hidden" name="section_id" value="{{ $sectionId }}">
                                                        <input type="hidden" name="feature_id" value="{{ $f->F_ID }}">
                                                        <input type="hidden" name="allow" value="{{ $allowedHere ? '0' : '1' }}">
                                                        <input class="form-check-input ob-hab-auto" type="checkbox"
                                                               style="cursor:pointer;" {{ $allowedHere ? 'checked' : '' }}>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            @endforeach
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
