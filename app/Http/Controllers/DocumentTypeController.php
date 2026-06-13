<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Document type & security configuration — manage `type_document` (category,
 * required feature to view, syndicate flag) for the library. Permission 47.
 * The per-document security levels (`document_security`) are shown read-only as
 * reference data.
 */
class DocumentTypeController extends Controller
{
    public function __construct(private readonly DocumentService $documents) {}

    public function index(): View
    {
        $features = $this->documents->features();
        $featureLabels = $features->pluck('F_LIBELLE', 'F_ID')->all();

        return view('document.types', [
            'types' => $this->documents->manageableTypes(),
            'features' => $features,
            'securities' => $this->documents->securities(),
            'columns' => $this->columns($featureLabels),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'code' => ['required', 'string', 'max:5', 'unique:type_document,TD_CODE'],
            'libelle' => ['required', 'string', 'max:50'],
            'security' => ['nullable', 'integer'],
            'syndicate' => ['nullable', 'boolean'],
        ]);

        DocumentType::create([
            'TD_CODE' => strtoupper($v['code']),
            'TD_LIBELLE' => $v['libelle'],
            'TD_SECURITY' => (int) ($v['security'] ?? 0),
            'TD_SYNDICATE' => (int) ($v['syndicate'] ?? 0),
        ]);

        return redirect()->route('document.types')->with('success', 'Type créé.');
    }

    public function update(Request $request, DocumentType $type): RedirectResponse
    {
        $v = $request->validate([
            'libelle' => ['required', 'string', 'max:50'],
            'security' => ['nullable', 'integer'],
            'syndicate' => ['nullable', 'boolean'],
        ]);

        $type->update([
            'TD_LIBELLE' => $v['libelle'],
            'TD_SECURITY' => (int) ($v['security'] ?? 0),
            'TD_SYNDICATE' => (int) ($v['syndicate'] ?? 0),
        ]);

        return redirect()->route('document.types')->with('success', 'Type mis à jour.');
    }

    public function destroy(DocumentType $type): RedirectResponse
    {
        if ($this->documents->typeInUse($type->TD_CODE)) {
            return redirect()->route('document.types')
                ->with('error', 'Ce type est utilisé par des documents ou dossiers : suppression impossible.');
        }

        $type->delete();

        return redirect()->route('document.types')->with('success', 'Type supprimé.');
    }

    /**
     * @param  array<int|string,string>  $featureLabels  F_ID => label
     */
    private function columns(array $featureLabels): array
    {
        return [
            ['key' => 'code', 'label' => 'Code', 'type' => 'text', 'alwaysVisible' => true,
                'value' => fn ($t) => $t->TD_CODE],
            ['key' => 'libelle', 'label' => 'Libellé', 'type' => 'text', 'alwaysVisible' => true,
                'value' => fn ($t) => $t->TD_LIBELLE],
            ['key' => 'security', 'label' => 'Visible si', 'type' => 'text',
                'value' => fn ($t) => (int) $t->TD_SECURITY === 0 ? 'Public' : ($featureLabels[(int) $t->TD_SECURITY] ?? 'F_ID '.$t->TD_SECURITY)],
            ['key' => 'syndicate', 'label' => 'Syndicat', 'type' => 'badge',
                'value' => fn ($t) => (int) $t->TD_SYNDICATE === 1 ? 'oui' : 'non',
                'badgeMap' => ['oui' => ['Oui', 'ob-badge-pres'], 'non' => ['Non', 'ob-badge-archive']]],
            ['key' => 'actions', 'label' => '', 'type' => 'html', 'alwaysVisible' => true,
                'value' => fn ($t) => '<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Modifier"'
                    .' data-type-edit data-code="'.e($t->TD_CODE).'" data-libelle="'.e($t->TD_LIBELLE).'"'
                    .' data-security="'.(int) $t->TD_SECURITY.'" data-syndicate="'.(int) $t->TD_SYNDICATE.'">'
                    .'<i class="fas fa-pen fa-xs"></i></button>'
                    .'<form method="POST" action="'.e(route('document.types.destroy', $t->TD_CODE)).'" class="d-inline ms-1" data-type-delete>'
                    .csrf_field().method_field('DELETE')
                    .'<button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Supprimer"><i class="fas fa-trash fa-xs"></i></button></form>'],
        ];
    }
}
