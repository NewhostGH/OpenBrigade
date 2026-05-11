@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Dps List</h1>
        <a href="{{ route('legacy_migrated.dps.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: dps.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>demande_pour_les_acteurs_</th>
                        <th>indicateur_p1</th>
                        <th>activit_du_rassemblement</th>
                        <th>indicateur_p2</th>
                        <th>caractristiques_de_lenvironnement_ou_de_laccessibilit_du_site</th>
                        <th>indicateur_e1</th>
                        <th>dlai_dintervention_des_secours_publics</th>
                        <th>indicateur_e2</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->demande_pour_les_acteurs_ ?? '' }}</td>
                        <td>{{ $item->indicateur_p1 ?? '' }}</td>
                        <td>{{ $item->activit_du_rassemblement ?? '' }}</td>
                        <td>{{ $item->indicateur_p2 ?? '' }}</td>
                        <td>{{ $item->caractristiques_de_lenvironnement_ou_de_laccessibilit_du_site ?? '' }}</td>
                        <td>{{ $item->indicateur_e1 ?? '' }}</td>
                        <td>{{ $item->dlai_dintervention_des_secours_publics ?? '' }}</td>
                        <td>{{ $item->indicateur_e2 ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.dps.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.dps.destroy', $itemKey) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?')">Delete</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>No key</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">No records found</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $items->links() }}
    </div>
</div>
@endsection
