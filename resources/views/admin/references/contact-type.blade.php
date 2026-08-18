@extends('layout.app')

@section('title', 'Identifiants de contact — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage', 'url' => route('admin.references')],
    ['label' => 'Identifiants de contact'],
]"/>

<div class="mx-3 mt-3">

    {{-- Flash messages (success/error) are rendered globally by layout.app --}}

    {{-- Add form --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Nouvel identifiant de contact</div>
        </div>
        <div class="p-3">
            <form method="POST" action="{{ route('admin.references.contact-type.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 ps-3" style="font-size:var(--font-size-sm);">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="CONTACT_TYPE" value="{{ old('CONTACT_TYPE') }}"
                               class="form-control form-control-sm" maxlength="20" required
                               style="width:180px;" placeholder="Ex. WhatsApp">
                    </div>
                    <div class="col-auto">
                        <label class="form-label form-label-sm">Classe d'icône <span class="text-danger">*</span></label>
                        <input type="text" name="CT_ICON" value="{{ old('CT_ICON') }}"
                               class="form-control form-control-sm" maxlength="40" required
                               style="width:220px;" placeholder="Ex. fab fa-whatsapp">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>{{ __('common.add') }}
                        </button>
                    </div>
                </div>
                <div class="form-text mt-1" style="font-size:var(--font-size-xs);">
                    La classe d'icône utilise Font Awesome (ex. <code>fab fa-skype</code>, <code>fas fa-broadcast-tower</code>).
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-address-card me-2"></i>Identifiants de contact ({{ $items->count() }})</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Nom</th>
                        <th>Classe d'icône</th>
                        <th style="width:110px;">Utilisations</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="align-middle text-center">
                            <i class="{{ $item->CT_ICON }}" title="{{ $item->CONTACT_TYPE }}"></i>
                        </td>
                        <td class="align-middle" colspan="2" style="font-size:var(--font-size-sm);">
                            <form method="POST" action="{{ route('admin.references.contact-type.update', $item->CT_ID) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="CONTACT_TYPE" value="{{ $item->CONTACT_TYPE }}"
                                       class="form-control form-control-sm" maxlength="20" required style="min-width:160px;">
                                <input type="text" name="CT_ICON" value="{{ $item->CT_ICON }}"
                                       class="form-control form-control-sm" maxlength="40" required style="min-width:200px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td class="align-middle text-muted" style="font-size:var(--font-size-sm);">{{ $item->nb_used }}</td>
                        <td class="align-middle text-end">
                            <form method="POST" action="{{ route('admin.references.contact-type.destroy', $item->CT_ID) }}"
                                  onsubmit="return confirm('Supprimer l\'identifiant « {{ addslashes($item->CONTACT_TYPE) }} » ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun identifiant de contact défini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
