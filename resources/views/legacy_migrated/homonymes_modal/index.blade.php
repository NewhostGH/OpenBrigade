@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Homonymes List</h1>
        <a href="{{ route('legacy_migrated.homonymes_modal.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: homonymes_modal.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>p_nom</th>
                        <th>p_prenom</th>
                        <th>date_formatp_birthdate</th>
                        <th>dmyp_birthdate0</th>
                        <th>p_birthplacep_birthplace0</th>
                        <th>p_sexe</th>
                        <th>p_id</th>
                        <th>p_prenom2</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->p_nom ?? '' }}</td>
                        <td>{{ $item->p_prenom ?? '' }}</td>
                        <td>{{ $item->date_formatp_birthdate ?? '' }}</td>
                        <td>{{ $item->dmyp_birthdate0 ?? '' }}</td>
                        <td>{{ $item->p_birthplacep_birthplace0 ?? '' }}</td>
                        <td>{{ $item->p_sexe ?? '' }}</td>
                        <td>{{ $item->p_id ?? '' }}</td>
                        <td>{{ $item->p_prenom2 ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.homonymes_modal.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.homonymes_modal.destroy', $itemKey) }}" class="d-inline">
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
