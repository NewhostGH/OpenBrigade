@extends('layout.app')

@section('title', 'Véhicules — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Véhicules</h1>
        @if(auth()->user()->hasPermission(17))
            <a href="{{ url('/legacy/ins_vehicule.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouveau véhicule
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('vehicule.index') }}" class="ob-filters">
        <div>
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm" placeholder="Immatriculation ou libellé…">
        </div>
        <div>
            <select name="status" class="form-select form-select-sm">
                <option value="all" @selected($status === 'all')>Tous</option>
                <option value="op"  @selected($status === 'op')>Opérationnels</option>
                <option value="nop" @selected($status === 'nop')>Non opérationnels</option>
            </select>
        </div>
        <div>
            <select name="section" class="form-select form-select-sm">
                <option value="0" @selected($filtSect === 0)>Ma section</option>
                @foreach($sections as $s)
                    <option value="{{ $s->S_ID }}" @selected($filtSect === $s->S_ID)>
                        {{ $s->S_CODE }} — {{ $s->S_DESCRIPTION }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="fas fa-filter me-1"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucun véhicule trouvé.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Immatriculation</th>
                    <th>Libellé</th>
                    <th>Statut</th>
                    <th>Assurance</th>
                    <th>Contrôle technique</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $v)
                    @php
                        $opLevel = $v->VP_OPERATIONNEL ?? 2;
                        $today   = now()->toDateString();
                        $assWarn = $v->V_ASS_DATE && $v->V_ASS_DATE <= now()->addDays(30)->toDateString();
                        $ctWarn  = $v->V_CT_DATE  && $v->V_CT_DATE  <= now()->addDays(30)->toDateString();
                    @endphp
                    <tr>
                        <td class="fw-semibold" style="font-size:var(--font-size-sm)">
                            {{ $v->V_IMMAT }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            <a href="{{ route('vehicule.show', $v->V_ID) }}" class="text-decoration-none">
                                {{ $v->V_LIBELLE }}
                            </a>
                        </td>
                        <td>
                            @if($opLevel >= 2)
                                <span class="badge bg-success">Opérationnel</span>
                            @elseif($opLevel === 1)
                                <span class="badge bg-warning text-dark">Limité</span>
                            @else
                                <span class="badge bg-danger">Indisponible</span>
                            @endif
                        </td>
                        <td style="font-size:var(--font-size-xs){{ $assWarn ? ';color:#c0392b;font-weight:600' : '' }}">
                            {{ $v->V_ASS_DATE ? \Carbon\Carbon::parse($v->V_ASS_DATE)->format('d/m/Y') : '—' }}
                            @if($assWarn) <i class="fas fa-exclamation-circle ms-1"></i> @endif
                        </td>
                        <td style="font-size:var(--font-size-xs){{ $ctWarn ? ';color:#c0392b;font-weight:600' : '' }}">
                            {{ $v->V_CT_DATE ? \Carbon\Carbon::parse($v->V_CT_DATE)->format('d/m/Y') : '—' }}
                            @if($ctWarn) <i class="fas fa-exclamation-circle ms-1"></i> @endif
                        </td>
                        <td>
                            <a href="{{ route('vehicule.show', $v->V_ID) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye fa-xs"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
