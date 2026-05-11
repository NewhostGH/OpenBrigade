@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Documents List</h1>
        <a href="{{ route('legacy_migrated.documents.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: documents.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>value</th>
                        <th>datastylebtndefaultdatacontainerbodylevelget_levelfiltermycolorget_color_levellevelclassstylebackgroundmycolordisplay_children21</th>
                        <th>0</th>
                        <th>filter</th>
                        <th>nbmaxlevels</th>
                        <th>sectionorderechoselectechoelseechoinputtypehiddennamesectionvaluefilterechodividdocumentsqueryselecttd_code</th>
                        <th>td_libelle</th>
                        <th>td_syndicate</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->value ?? '' }}</td>
                        <td>{{ $item->datastylebtndefaultdatacontainerbodylevelget_levelfiltermycolorget_color_levellevelclassstylebackgroundmycolordisplay_children21 ?? '' }}</td>
                        <td>{{ $item->0 ?? '' }}</td>
                        <td>{{ $item->filter ?? '' }}</td>
                        <td>{{ $item->nbmaxlevels ?? '' }}</td>
                        <td>{{ $item->sectionorderechoselectechoelseechoinputtypehiddennamesectionvaluefilterechodividdocumentsqueryselecttd_code ?? '' }}</td>
                        <td>{{ $item->td_libelle ?? '' }}</td>
                        <td>{{ $item->td_syndicate ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.documents.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.documents.destroy', $itemKey) }}" class="d-inline">
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
