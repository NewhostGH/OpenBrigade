@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Groupe List</h1>
        <a href="{{ route('legacy_migrated.ins_groupe.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: ins_groupe.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>tr_sub_possible</th>
                        <th>tr_all_possible</th>
                        <th>gp_usage</th>
                        <th>gp_astreinte</th>
                        <th>gp_order</th>
                        <th>tr_config</th>
                        <th>externeseulementoptionifgp_usageallselectedselectedelseselectedechooptionvalueallselectedstylebackgroundyellowinterneetexterneoptionechoselecttdechotrelseiftr_sub_possible1checkedcheckedelsecheckedechotrtdalignleftbmembredunesoussectionpossiblebtdtdalignleftcolspan2inputtypecheckboxnamesub_possiblecheckedvalue1titlesicettecaseestcoche</th>
                        <th>alorsunmembredunesoussectionpeutavoirlerletdechotriftr_all_possible1checkedcheckedelsecheckedechotrtdbmembredenimportequellesectionbtdtdalignleftcolspan2inputtypecheckboxnameall_possiblevalue1checkedtitlesicettecaseestcoche</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->tr_sub_possible ?? '' }}</td>
                        <td>{{ $item->tr_all_possible ?? '' }}</td>
                        <td>{{ $item->gp_usage ?? '' }}</td>
                        <td>{{ $item->gp_astreinte ?? '' }}</td>
                        <td>{{ $item->gp_order ?? '' }}</td>
                        <td>{{ $item->tr_config ?? '' }}</td>
                        <td>{{ $item->externeseulementoptionifgp_usageallselectedselectedelseselectedechooptionvalueallselectedstylebackgroundyellowinterneetexterneoptionechoselecttdechotrelseiftr_sub_possible1checkedcheckedelsecheckedechotrtdalignleftbmembredunesoussectionpossiblebtdtdalignleftcolspan2inputtypecheckboxnamesub_possiblecheckedvalue1titlesicettecaseestcoche ?? '' }}</td>
                        <td>{{ $item->alorsunmembredunesoussectionpeutavoirlerletdechotriftr_all_possible1checkedcheckedelsecheckedechotrtdbmembredenimportequellesectionbtdtdalignleftcolspan2inputtypecheckboxnameall_possiblevalue1checkedtitlesicettecaseestcoche ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.ins_groupe.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.ins_groupe.destroy', $itemKey) }}" class="d-inline">
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
