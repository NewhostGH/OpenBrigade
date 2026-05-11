@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Message List</h1>
        <a href="{{ route('legacy_migrated.message.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: message.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>maxm_id1</th>
                        <th>now</th>
                        <th>p_email</th>
                        <th>nametm_idclassformcontrolselectcontroldatastylebtndefaultqueryselecttm_id</th>
                        <th>tm_libelle</th>
                        <th>tm_color</th>
                        <th>tm_icon</th>
                        <th>namedureeclassformcontrolselectcontroldatastylebtndefaultoptionvalue11joursoptionoptionvalue12joursoptionoptionvalue33joursoptionoptionvalue44joursoptionoptionvalue55joursoptionoptionvalue66joursoptionoptionvalue7selected7joursoptionoptionvalue1010joursoptionoptionvalue1515joursoptionoptionvalue2020joursoptionoptionvalue3030joursoptionoptionvalue6060joursoptionoptionvalue0sanslimitationoptionselecttdtrchoixsectionhighestsectionget_highest_section_where_grantedid</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->maxm_id1 ?? '' }}</td>
                        <td>{{ $item->now ?? '' }}</td>
                        <td>{{ $item->p_email ?? '' }}</td>
                        <td>{{ $item->nametm_idclassformcontrolselectcontroldatastylebtndefaultqueryselecttm_id ?? '' }}</td>
                        <td>{{ $item->tm_libelle ?? '' }}</td>
                        <td>{{ $item->tm_color ?? '' }}</td>
                        <td>{{ $item->tm_icon ?? '' }}</td>
                        <td>{{ $item->namedureeclassformcontrolselectcontroldatastylebtndefaultoptionvalue11joursoptionoptionvalue12joursoptionoptionvalue33joursoptionoptionvalue44joursoptionoptionvalue55joursoptionoptionvalue66joursoptionoptionvalue7selected7joursoptionoptionvalue1010joursoptionoptionvalue1515joursoptionoptionvalue2020joursoptionoptionvalue3030joursoptionoptionvalue6060joursoptionoptionvalue0sanslimitationoptionselecttdtrchoixsectionhighestsectionget_highest_section_where_grantedid ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.message.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.message.destroy', $itemKey) }}" class="d-inline">
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
