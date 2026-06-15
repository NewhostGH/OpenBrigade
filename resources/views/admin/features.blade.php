@extends('layout.app')

@section('title', 'Fonctionnalités — ' . config('app.name'))

@push('scripts')
    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.ob-feature-toggle').forEach(function (el) {
                    el.addEventListener('change', function () {
                        this.closest('form').submit();
                    });
                });
            });
        })();
    </script>
@endpush

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => 'Administration'],
            ['label' => 'Fonctionnalités'],
        ]" />

    <div class="px-3 px-md-4 mt-3">
        @foreach($groups as $key => $group)
            @if($features->has($key))
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-{{ $group['icon'] }} me-2"></i>{{ $group['label'] }}
                        </div>
                    </div>

                    <table class="table table-sm table-hover mb-0">
                        <tbody>
                            @foreach($features->get($key) as $feature)
                                <tr>
                                    <td class="px-2" style="width:100%;vertical-align:middle;font-size:var(--font-size-sm);">
                                        <span class="fw-semibold">
                                            @if($feature->icon)
                                                <i class="fas fa-{{ $feature->icon }} me-1"></i>
                                            @endif
                                            {{ $feature->name }}
                                        </span>
                                        @if($feature->status === 'wip')
                                            <span class="ob-badge ob-badge-ext ms-1"
                                                title="Pas encore disponible dans l'application native (en cours de migration).">WIP</span>
                                        @endif
                                        @if($feature->description)
                                            <div class="text-muted" style="font-size:var(--font-size-xs);">
                                                {{ $feature->description }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <form method="POST" action="{{ route('admin.features.toggle', $feature) }}">
                                            @csrf @method('PATCH')
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input ob-feature-toggle" name="enabled"
                                                    value="1" @checked($feature->enabled)>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    </div>

@endsection