@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: cotisation_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">CotisationEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.cotisation_edit.update', $itemKey) : route('legacy_migrated.cotisation_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="date_rejet" class="form-label">Date Rejet</label>
            <input type="text" id="date_rejet" name="date_rejet" class="form-control" value="{{ old('date_rejet', $item?->date_rejet) }}">
            @error('date_rejet')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="representer" class="form-label">Representer</label>
            <input type="text" id="representer" name="representer" class="form-control" value="{{ old('representer', $item?->representer) }}">
            @error('representer')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="regularise" class="form-label">Regularise</label>
            <input type="text" id="regularise" name="regularise" class="form-control" value="{{ old('regularise', $item?->regularise) }}">
            @error('regularise')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_regul" class="form-label">Date Regul</label>
            <input type="text" id="date_regul" name="date_regul" class="form-control" value="{{ old('date_regul', $item?->date_regul) }}">
            @error('date_regul')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="date_paiement" class="form-label">Date Paiement</label>
            <input type="text" id="date_paiement" name="date_paiement" class="form-control" value="{{ old('date_paiement', $item?->date_paiement) }}">
            @error('date_paiement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="observation" class="form-label">Observation</label>
            <textarea id="observation" name="observation" class="form-control" rows="4">{{ old('observation', $item?->observation) }}</textarea>
            @error('observation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="commentaire" class="form-label">Commentaire</label>
            <textarea id="commentaire" name="commentaire" class="form-control" rows="4">{{ old('commentaire', $item?->commentaire) }}</textarea>
            @error('commentaire')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="periode" class="form-label">Periode</label>
            <textarea id="periode" name="periode" class="form-control" rows="4">{{ old('periode', $item?->periode) }}</textarea>
            @error('periode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="defaut_bancaire" class="form-label">Defaut Bancaire</label>
            <textarea id="defaut_bancaire" name="defaut_bancaire" class="form-control" rows="4">{{ old('defaut_bancaire', $item?->defaut_bancaire) }}</textarea>
            @error('defaut_bancaire')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type_regularisation" class="form-label">Type Regularisation</label>
            <textarea id="type_regularisation" name="type_regularisation" class="form-control" rows="4">{{ old('type_regularisation', $item?->type_regularisation) }}</textarea>
            @error('type_regularisation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type_paiement" class="form-label">Type Paiement</label>
            <textarea id="type_paiement" name="type_paiement" class="form-control" rows="4">{{ old('type_paiement', $item?->type_paiement) }}</textarea>
            @error('type_paiement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="compte_a_debiter" class="form-label">Compte A Debiter</label>
            <textarea id="compte_a_debiter" name="compte_a_debiter" class="form-control" rows="4">{{ old('compte_a_debiter', $item?->compte_a_debiter) }}</textarea>
            @error('compte_a_debiter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="rejet_id" class="form-label">Rejet Id</label>
            <input type="text" id="rejet_id" name="rejet_id" class="form-control" value="{{ old('rejet_id', $item?->rejet_id) }}">
            @error('rejet_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="paiement_id" class="form-label">Paiement Id</label>
            <input type="text" id="paiement_id" name="paiement_id" class="form-control" value="{{ old('paiement_id', $item?->paiement_id) }}">
            @error('paiement_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="rembourse" class="form-label">Rembourse</label>
            <input type="text" id="rembourse" name="rembourse" class="form-control" value="{{ old('rembourse', $item?->rembourse) }}">
            @error('rembourse')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="pid" class="form-label">Pid</label>
            <input type="text" id="pid" name="pid" class="form-control" value="{{ old('pid', $item?->pid) }}">
            @error('pid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <input type="text" id="note" name="note" class="form-control" value="{{ old('note', $item?->note) }}">
            @error('note')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="action" class="form-label">Action</label>
            <input type="text" id="action" name="action" class="form-control" value="{{ old('action', $item?->action) }}">
            @error('action')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="annee" class="form-label">Annee</label>
            <input type="text" id="annee" name="annee" class="form-control" value="{{ old('annee', $item?->annee) }}">
            @error('annee')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="montant_regul" class="form-label">Montant Regul</label>
            <input type="text" id="montant_regul" name="montant_regul" class="form-control" value="{{ old('montant_regul', $item?->montant_regul) }}">
            @error('montant_regul')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.cotisation_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
