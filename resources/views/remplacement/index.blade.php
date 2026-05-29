@extends('layout.app')

@section('title', 'Remplacements — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Remplacements de garde</h1>
        <a href="{{ url('/legacy/remplacement_edit.php') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Demander un remplacement
        </a>
    </div>

    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('remplacement.index', ['tab' => 'mine']) }}"
           class="btn btn-sm {{ $tab === 'mine' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="fas fa-user me-1"></i> Mes remplacements
        </a>
        <a href="{{ route('remplacement.index', ['tab' => 'section']) }}"
           class="btn btn-sm {{ $tab === 'section' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="fas fa-users me-1"></i> Ma section
        </a>
    </div>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucun remplacement.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Activité</th>
                    <th>Date</th>
                    <th>Remplacé</th>
                    <th>Remplaçant</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $r)
                    @php
                        if ($r->APPROVED)       $badge = ['bg-success', 'Approuvé'];
                        elseif ($r->REJECTED)   $badge = ['bg-danger', 'Refusé'];
                        elseif ($r->ACCEPTED)   $badge = ['bg-info text-dark', 'Accepté'];
                        else                    $badge = ['bg-warning text-dark', 'En attente'];
                    @endphp
                    <tr>
                        <td style="font-size:var(--font-size-sm)">
                            <a href="{{ route('evenement.show', $r->E_CODE) }}" class="text-decoration-none">
                                {{ $r->E_LIBELLE ?? $r->E_CODE }}
                            </a>
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ $r->EH_DATE_DEBUT ? \Carbon\Carbon::parse($r->EH_DATE_DEBUT)->format('d/m/Y') : '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">{{ $r->replaced_name }}</td>
                        <td style="font-size:var(--font-size-sm)">{{ $r->substitute_name }}</td>
                        <td><span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
