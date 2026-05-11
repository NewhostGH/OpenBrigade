@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Personnel List</h1>
        <a href="{{ route('legacy_migrated.ins_personnel.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: ins_personnel.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>tp_code</th>
                        <th>tp_description</th>
                        <th>tp_descriptionifprofessiontp_codeselectedselectedelseselectedechooptionvaluetp_codeselectedtp_descriptionoptionechoselectechotrlignegradeifgrades1andfullg_gradeechotrtdbgradebasterisktdtdalignleftquery2query_gradesresult2mysqli_querydbc</th>
                        <th>orderbys_descriptionresult2mysqli_querydbc</th>
                        <th>query2echotrtdbstatutbasterisktdtdalignleftselectnamestatutidstatutclassformcontrolformcontrolsmdisabledonchangejavascriptchangedtypeinswhilecustom_fetch_arrayresult2selectedifstatuts_statutselectedselectedelseifstatutextands_statutextselectedselectedelseifstatutands_statutresandarmyselectedselectedelseifstatutands_statutadhandsyndicateselectedselectedelseifstatutands_statutbenors_statutspvselectedselectedelseselectedifs_statutextstyleext_styleelsestyleother_styleechooptionvalues_statutselectedstyles_descriptionoptionechoselecttrparticularitsdessppifstatutsppstyleelsestylestyledisplaynoneechotridtspprowstyleclasspad0trcolortdbrgimetravailbasterisktdtdalignleftechoselectnameregime_travailidregime_travailtitlechoisirlergimedetravailquery2selecttrt_code</th>
                        <th>trt_desc</th>
                        <th>ts_code</th>
                        <th>ts_libelle</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->tp_code ?? '' }}</td>
                        <td>{{ $item->tp_description ?? '' }}</td>
                        <td>{{ $item->tp_descriptionifprofessiontp_codeselectedselectedelseselectedechooptionvaluetp_codeselectedtp_descriptionoptionechoselectechotrlignegradeifgrades1andfullg_gradeechotrtdbgradebasterisktdtdalignleftquery2query_gradesresult2mysqli_querydbc ?? '' }}</td>
                        <td>{{ $item->orderbys_descriptionresult2mysqli_querydbc ?? '' }}</td>
                        <td>{{ $item->query2echotrtdbstatutbasterisktdtdalignleftselectnamestatutidstatutclassformcontrolformcontrolsmdisabledonchangejavascriptchangedtypeinswhilecustom_fetch_arrayresult2selectedifstatuts_statutselectedselectedelseifstatutextands_statutextselectedselectedelseifstatutands_statutresandarmyselectedselectedelseifstatutands_statutadhandsyndicateselectedselectedelseifstatutands_statutbenors_statutspvselectedselectedelseselectedifs_statutextstyleext_styleelsestyleother_styleechooptionvalues_statutselectedstyles_descriptionoptionechoselecttrparticularitsdessppifstatutsppstyleelsestylestyledisplaynoneechotridtspprowstyleclasspad0trcolortdbrgimetravailbasterisktdtdalignleftechoselectnameregime_travailidregime_travailtitlechoisirlergimedetravailquery2selecttrt_code ?? '' }}</td>
                        <td>{{ $item->trt_desc ?? '' }}</td>
                        <td>{{ $item->ts_code ?? '' }}</td>
                        <td>{{ $item->ts_libelle ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.ins_personnel.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.ins_personnel.destroy', $itemKey) }}" class="d-inline">
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
