@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">TypeConsommable List</h1>
        <a href="{{ route('legacy_migrated.upd_type_consommable.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: upd_type_consommable.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>tc_id</th>
                        <th>cc_code</th>
                        <th>tc_description</th>
                        <th>tc_conditionnement</th>
                        <th>tc_unite_mesure</th>
                        <th>cc_name</th>
                        <th>cc_description</th>
                        <th>cc_image</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->tc_id ?? '' }}</td>
                        <td>{{ $item->cc_code ?? '' }}</td>
                        <td>{{ $item->tc_description ?? '' }}</td>
                        <td>{{ $item->tc_conditionnement ?? '' }}</td>
                        <td>{{ $item->tc_unite_mesure ?? '' }}</td>
                        <td>{{ $item->cc_name ?? '' }}</td>
                        <td>{{ $item->cc_description ?? '' }}</td>
                        <td>{{ $item->cc_image ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.upd_type_consommable.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.upd_type_consommable.destroy', $itemKey) }}" class="d-inline">
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
