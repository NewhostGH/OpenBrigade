@extends('layout.app')

@section('title', ($event->E_LIBELLE ?? $event->E_CODE) . ' — Demande de renfort — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Activités', 'url' => route('event.index')],
    ['label' => $event->E_LIBELLE ?? $event->E_CODE, 'url' => route('event.show', $event->E_CODE)],
    ['label' => 'Demande de renfort'],
]"/>

<div class="mx-3 mt-3">
    <form method="POST" action="{{ route('event.renfort-request.update', $event->E_CODE) }}">
        @csrf

        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-ambulance me-1"></i>
                    Demande de renfort — {{ $event->E_LIBELLE ?? $event->E_CODE }}
                </div>
                <a href="{{ route('event.show', $event->E_CODE) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>

            {{-- Véhicules ──────────────────────────────────────────── --}}
            <div class="ob-widget-card-body border-bottom">
                <h6 class="fw-semibold mb-3" style="font-size:var(--font-size-sm)">
                    <i class="fas fa-truck me-1 text-muted"></i> Véhicules requis
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-sm-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Nombre total de véhicules</label>
                        <input type="number" name="nb_vehicules" class="form-control form-control-sm"
                               min="0" max="9999"
                               value="{{ old('nb_vehicules', $global?->NB_VEHICULES ?? 0) }}">
                    </div>
                    <div class="col-sm-9">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Point de regroupement</label>
                        <input type="text" name="point_regroupement" class="form-control form-control-sm"
                               maxlength="250"
                               value="{{ old('point_regroupement', $global?->POINT_REGROUPEMENT ?? '') }}">
                    </div>
                </div>

                @if($vehicleTypes->isNotEmpty())
                <div class="mb-3">
                    <label class="form-label" style="font-size:var(--font-size-sm)">Détail par type de véhicule</label>
                    <div class="row g-2">
                        @php $prevUsage = ''; @endphp
                        @foreach($vehicleTypes as $vt)
                        @if($prevUsage !== $vt->TV_USAGE)
                            @if($loop->index > 0)</div>@endif
                            <div class="col-12 mt-1 mb-0">
                                <small class="text-muted text-uppercase" style="font-size:var(--font-size-xs);letter-spacing:.05em">{{ $vt->TV_USAGE }}</small>
                            </div>
                            @php $prevUsage = $vt->TV_USAGE; @endphp
                        @endif
                        <div class="col-6 col-sm-4 col-md-3">
                            <label class="form-label mb-0" style="font-size:var(--font-size-xs)">{{ $vt->TV_CODE }} — {{ $vt->TV_LIBELLE }}</label>
                            <input type="number" name="vehicle_types[{{ $vt->TV_CODE }}]"
                                   class="form-control form-control-sm"
                                   min="0" max="999"
                                   value="{{ old('vehicle_types.'.$vt->TV_CODE, $assignedVehicleCodes[$vt->TV_CODE] ?? 0) }}">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Matériel ──────────────────────────────────────────── --}}
            @if($materialCategories->isNotEmpty())
            <div class="ob-widget-card-body border-bottom">
                <h6 class="fw-semibold mb-3" style="font-size:var(--font-size-sm)">
                    <i class="fas fa-box me-1 text-muted"></i> Catégories de matériel requis
                </h6>
                <div class="row g-2">
                    @foreach($materialCategories as $cat)
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="categories[]"
                                   value="{{ $cat->TM_USAGE }}"
                                   id="cat_{{ $cat->TM_USAGE }}"
                                   @checked(in_array($cat->TM_USAGE, old('categories', $assignedCategories->toArray())))>
                            <label class="form-check-label" for="cat_{{ $cat->TM_USAGE }}"
                                   style="font-size:var(--font-size-sm)">
                                @if($cat->PICTURE)
                                    <i class="fas fa-{{ $cat->PICTURE }} me-1 text-muted"></i>
                                @endif
                                {{ $cat->TM_USAGE }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Demande spécifique ────────────────────────────────── --}}
            <div class="ob-widget-card-body">
                <label class="form-label" style="font-size:var(--font-size-sm)">Demande spécifique</label>
                <textarea name="demande_specifique" class="form-control form-control-sm"
                          rows="3" maxlength="600"
                          >{{ old('demande_specifique', $global?->DEMANDE_SPECIFIQUE ?? '') }}</textarea>
            </div>

            @if(auth()->user()->hasPermission(15))
            <div class="ob-widget-card-footer text-end">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-save me-1"></i> Enregistrer
                </button>
            </div>
            @endif
        </div>

    </form>
</div>

@push('scripts')
<script>
(function () {
    const totalInput = document.querySelector('input[name="nb_vehicules"]');
    const typeInputs = Array.from(document.querySelectorAll('input[name^="vehicle_types["]'));
    if (!totalInput || typeInputs.length === 0) return;

    // The total may legitimately exceed the per-type breakdown (e.g. "5 vehicles,
    // any type"). We only auto-raise it to the sum so the total is never lower
    // than the detail; a manually-entered higher total is preserved.
    function syncTotal() {
        const sum = typeInputs.reduce((acc, el) => acc + (parseInt(el.value, 10) || 0), 0);
        if ((parseInt(totalInput.value, 10) || 0) < sum) {
            totalInput.value = sum;
        }
    }
    typeInputs.forEach(el => el.addEventListener('input', syncTotal));
})();
</script>
@endpush

@endsection
