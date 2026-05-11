@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">FonctionsChart List</h1>
        <a href="{{ route('legacy_migrated.fonctions_chart.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: fonctions_chart.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>functiongeneratedatavardatasetnforeachdataset</th>
                        <th>jsscriptbrowserfunctionrepo_connexion_heure_journeesection</th>
                        <th>subsectionsglobaldbc</th>
                        <th>a_debut</th>
                        <th>count1</th>
                        <th>dm</th>
                        <th>substring_indexa_browser</th>
                        <th>1</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->functiongeneratedatavardatasetnforeachdataset ?? '' }}</td>
                        <td>{{ $item->jsscriptbrowserfunctionrepo_connexion_heure_journeesection ?? '' }}</td>
                        <td>{{ $item->subsectionsglobaldbc ?? '' }}</td>
                        <td>{{ $item->a_debut ?? '' }}</td>
                        <td>{{ $item->count1 ?? '' }}</td>
                        <td>{{ $item->dm ?? '' }}</td>
                        <td>{{ $item->substring_indexa_browser ?? '' }}</td>
                        <td>{{ $item->1 ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.fonctions_chart.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.fonctions_chart.destroy', $itemKey) }}" class="d-inline">
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
