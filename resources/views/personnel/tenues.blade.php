@extends('layout.app')

@section('title', strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM . ' — Dotation — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Personnel', 'url' => route('personnel.index')],
    ['label' => strtoupper($personnel->P_NOM).' '.ucfirst(mb_strtolower($personnel->P_PRENOM)), 'url' => route('personnel.show', $personnel)],
    ['label' => 'Dotation habillement'],
]"/>

<div class="mx-3 mt-3">
    <form method="POST" action="{{ route('personnel.tenues.update', $personnel) }}">
        @csrf

        {{-- ▸ Dotation existante ──────────────────────────────────────────── --}}
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-box me-1"></i>
                    Habillement en dotation — {{ strtoupper($personnel->P_NOM) }} {{ ucfirst(mb_strtolower($personnel->P_PRENOM)) }}
                </div>
                <div class="ob-widget-card-actions">
                    <span class="badge bg-secondary me-2">{{ $assigned->count() }} article(s)</span>
                    <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
            </div>

            @if($assigned->isEmpty())
                <div class="ob-widget-card-body">
                    <p class="ob-widget-empty mb-0">Aucune dotation habillement enregistrée.</p>
                </div>
            @else
                <div class="ob-widget-card-body p-0">
                    <table class="ob-table ob-table-sm w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Modèle</th>
                                <th>Année</th>
                                <th>Taille</th>
                                <th class="text-end" style="width:70px">Nb</th>
                                @if($canFullUpdate)<th style="width:40px"></th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assigned as $item)
                            @php $sizes = $sizesByCode[$item->TT_CODE] ?? collect(); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $item->TM_CODE }}</td>
                                <td>
                                    @if($canFullUpdate)
                                        <input type="text" name="items[{{ $item->MA_ID }}][modele]"
                                               value="{{ $item->MA_MODELE }}"
                                               class="form-control form-control-sm" maxlength="40" style="min-width:120px">
                                    @else
                                        {{ $item->MA_MODELE }}
                                    @endif
                                </td>
                                <td>
                                    @if($canFullUpdate)
                                        <input type="number" name="items[{{ $item->MA_ID }}][annee]"
                                               value="{{ $item->MA_ANNEE }}"
                                               class="form-control form-control-sm" min="1900" max="2099" style="width:80px">
                                    @else
                                        {{ $item->MA_ANNEE }}
                                    @endif
                                </td>
                                <td>
                                    @if($item->TT_CODE === 'NONE' || $sizes->isEmpty())
                                        <span class="text-muted">—</span>
                                        @if($canFullUpdate || $canSizeOnly)
                                            <input type="hidden" name="items[{{ $item->MA_ID }}][tv_id]" value="0">
                                        @endif
                                    @elseif($canFullUpdate || $canSizeOnly)
                                        <select name="items[{{ $item->MA_ID }}][tv_id]"
                                                class="form-select form-select-sm" style="min-width:100px">
                                            <option value="0">— taille —</option>
                                            @foreach($sizes as $sz)
                                                <option value="{{ $sz->TV_ID }}" @selected($sz->TV_ID == $item->TV_ID)>
                                                    {{ $sz->TV_NAME }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $item->current_size ?? '—' }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($canFullUpdate)
                                        <input type="number" name="items[{{ $item->MA_ID }}][nb]"
                                               value="{{ $item->MA_NB }}"
                                               class="form-control form-control-sm text-end" min="0" max="99"
                                               style="width:60px" title="0 = supprimer">
                                    @else
                                        {{ $item->MA_NB }}
                                    @endif
                                </td>
                                @if($canFullUpdate)
                                <td class="text-center text-muted" style="font-size:var(--font-size-xs)">
                                    <span title="Mettre Nb à 0 pour supprimer"><i class="fas fa-info-circle"></i></span>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($canFullUpdate || ($canSizeOnly && $assigned->isNotEmpty()))
                <div class="ob-widget-card-footer text-end">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            @endif
        </div>

        {{-- ▸ Ajouter des articles (perm 70 seulement) ───────────────────── --}}
        @if($canFullUpdate && $available->isNotEmpty())
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-plus me-1"></i> Ajouter habillement
                </div>
            </div>
            <div class="ob-widget-card-body p-0">
                <table class="ob-table ob-table-sm w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Modèle</th>
                            <th>Année</th>
                            <th>Taille</th>
                            <th class="text-end" style="width:70px">Nb</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($available as $type)
                        @php $sizes = $sizesByCode[$type->TT_CODE] ?? collect(); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $type->TM_CODE }}</td>
                            <td>
                                <input type="text" name="new[{{ $type->TM_ID }}][modele]"
                                       class="form-control form-control-sm" maxlength="40" style="min-width:120px">
                            </td>
                            <td>
                                <input type="number" name="new[{{ $type->TM_ID }}][annee]"
                                       class="form-control form-control-sm" min="1900" max="2099"
                                       placeholder="{{ date('Y') }}" style="width:80px">
                            </td>
                            <td>
                                @if($type->TT_CODE === 'NONE' || $sizes->isEmpty())
                                    <span class="text-muted">—</span>
                                    <input type="hidden" name="new[{{ $type->TM_ID }}][tv_id]" value="0">
                                @else
                                    <select name="new[{{ $type->TM_ID }}][tv_id]"
                                            class="form-select form-select-sm" style="min-width:100px">
                                        <option value="0">— taille —</option>
                                        @foreach($sizes as $sz)
                                            <option value="{{ $sz->TV_ID }}">{{ $sz->TV_NAME }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td class="text-end">
                                <input type="number" name="new[{{ $type->TM_ID }}][nb]"
                                       value="0" class="form-control form-control-sm text-end"
                                       min="0" max="99" style="width:60px">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ob-widget-card-footer text-end">
                <small class="text-muted me-3">Nb = 0 : non ajouté</small>
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-save me-1"></i> Enregistrer
                </button>
            </div>
        </div>
        @endif

    </form>
</div>

@endsection
