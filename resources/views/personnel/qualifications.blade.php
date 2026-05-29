@extends('layout.app')

@section('title', 'Qualifications — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Qualifications de la section</h1>
    </div>

    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('personnel.qualifications', ['filter' => 'all']) }}"
           class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">Toutes</a>
        <a href="{{ route('personnel.qualifications', ['filter' => 'expiring']) }}"
           class="btn btn-sm {{ $filter === 'expiring' ? 'btn-warning' : 'btn-outline-secondary' }}">
            <i class="fas fa-clock me-1"></i> Expirant bientôt
        </a>
        <a href="{{ route('personnel.qualifications', ['filter' => 'expired']) }}"
           class="btn btn-sm {{ $filter === 'expired' ? 'btn-danger' : 'btn-outline-secondary' }}">
            <i class="fas fa-exclamation-circle me-1"></i> Expirées
        </a>
    </div>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucune qualification.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Personnel</th>
                    <th>Type</th>
                    <th>Valeur</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $q)
                    <tr>
                        <td>
                            <a href="{{ route('personnel.show', $q->P_ID) }}"
                               class="text-decoration-none" style="font-size:var(--font-size-sm)">
                                {{ $q->P_PRENOM }} {{ strtoupper($q->P_NOM) }}
                            </a>
                        </td>
                        <td style="font-size:var(--font-size-sm)">{{ $q->PS_TYPE ?? '—' }}</td>
                        <td style="font-size:var(--font-size-sm)">{{ $q->Q_VAL ?? '—' }}</td>
                        <td style="font-size:var(--font-size-sm){{ $q->status === 'expired' ? ';color:#c0392b;font-weight:600' : ($q->status === 'expiring' ? ';color:#e67e22;font-weight:600' : '') }}">
                            {{ $q->Q_EXPIRATION ? \Carbon\Carbon::parse($q->Q_EXPIRATION)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            @if($q->status === 'expired')
                                <span class="badge bg-danger">Expirée</span>
                            @elseif($q->status === 'expiring')
                                <span class="badge bg-warning text-dark">Bientôt</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
