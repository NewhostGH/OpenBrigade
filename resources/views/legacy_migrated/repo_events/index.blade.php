@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">RepoEvents List</h1>
        <a href="{{ route('legacy_migrated.repo_events.create') }}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{ request('query') }}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: repo_events.php | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>value</th>
                        <th>echooptgrouplabelutilisationshow_option0</th>
                        <th>connexionsparsectionshow_option23</th>
                        <th>systmesdexploitationutilissshow_option24</th>
                        <th>navigateursutilissshow_option67</th>
                        <th>connexionsparheuredelajourneshow_option68</th>
                        <th>connexionsparjourdelasemaineshow_option69</th>
                        <th>derniersshow_option70</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
                        <td>{{ $item->value ?? '' }}</td>
                        <td>{{ $item->echooptgrouplabelutilisationshow_option0 ?? '' }}</td>
                        <td>{{ $item->connexionsparsectionshow_option23 ?? '' }}</td>
                        <td>{{ $item->systmesdexploitationutilissshow_option24 ?? '' }}</td>
                        <td>{{ $item->navigateursutilissshow_option67 ?? '' }}</td>
                        <td>{{ $item->connexionsparheuredelajourneshow_option68 ?? '' }}</td>
                        <td>{{ $item->connexionsparjourdelasemaineshow_option69 ?? '' }}</td>
                        <td>{{ $item->derniersshow_option70 ?? '' }}</td>
                        <td>
                            @if($itemKey)
                                <a href="{{ route('legacy_migrated.repo_events.edit', $itemKey) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('legacy_migrated.repo_events.destroy', $itemKey) }}" class="d-inline">
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
