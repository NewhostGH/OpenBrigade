@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">EvenementNotify List</h1>
        <a href="{{ route('legacy_migrated.evenement_notify.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: evenement_notify.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>e_code</th>
                        <th>eh_id_eh_id</th>
                        <th>s_id</th>
                        <th>te_code</th>
                        <th>te_libelle</th>
                        <th>e_lieu</th>
                        <th>eh_date_debut_eh_date_debut</th>
                        <th>eh_date_fin_eh_date_fin</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->e_code ?? '' }}</td>
                        <td>{{ $item->eh_id_eh_id ?? '' }}</td>
                        <td>{{ $item->s_id ?? '' }}</td>
                        <td>{{ $item->te_code ?? '' }}</td>
                        <td>{{ $item->te_libelle ?? '' }}</td>
                        <td>{{ $item->e_lieu ?? '' }}</td>
                        <td>{{ $item->eh_date_debut_eh_date_debut ?? '' }}</td>
                        <td>{{ $item->eh_date_fin_eh_date_fin ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.evenement_notify.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.evenement_notify.destroy', $itemKey) }}" class="d-inline">
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
