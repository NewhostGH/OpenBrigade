<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    // ── Overview ──────────────────────────────────────────────────────────────

    public function index(): View
    {
        $counts = [
            'type_evenement' => DB::table('type_evenement')->count(),
            'type_participation' => DB::table('type_participation')->count(),
            'type_materiel' => DB::table('type_materiel')->count(),
            'type_consommable' => DB::table('type_consommable')->count(),
            'categorie_evenement' => DB::table('categorie_evenement')->count(),
            'grade' => DB::table('grade')->count(),
        ];

        return view('admin.references.index', compact('counts'));
    }

    // ── Type Événement ────────────────────────────────────────────────────────

    public function eventTypeIndex(): View
    {
        $items = DB::table('type_evenement as te')
            ->leftJoin('categorie_evenement as c', 'c.CEV_CODE', '=', 'te.CEV_CODE')
            ->orderBy('c.CEV_DESCRIPTION')
            ->orderBy('te.TE_LIBELLE')
            ->select('te.TE_CODE', 'te.TE_LIBELLE', 'te.CEV_CODE', 'c.CEV_DESCRIPTION',
                'te.ORDRE_MISSION', 'te.CONVOCATIONS', 'te.FICHE_PRESENCE')
            ->get();

        $categories = DB::table('categorie_evenement')->orderBy('CEV_DESCRIPTION')->get();

        return view('admin.references.event-type', compact('items', 'categories'));
    }

    public function eventTypeStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'TE_CODE' => ['required', 'string', 'max:5', 'unique:type_evenement,TE_CODE'],
            'TE_LIBELLE' => ['required', 'string', 'max:40'],
            'CEV_CODE' => ['required', 'string', 'max:5', 'exists:categorie_evenement,CEV_CODE'],
        ]);

        DB::table('type_evenement')->insert([
            'TE_CODE' => strtoupper($v['TE_CODE']),
            'TE_LIBELLE' => $v['TE_LIBELLE'],
            'CEV_CODE' => $v['CEV_CODE'],
            'TE_MAIN_COURANTE' => 0,
            'TE_VICTIMES' => 0,
            'TE_MULTI_DUPLI' => 0,
            'EVAL_PAR_STAGIAIRES' => 0,
            'PROCES_VERBAL' => 0,
            'FICHE_PRESENCE' => $request->boolean('FICHE_PRESENCE') ? 1 : 0,
            'ORDRE_MISSION' => $request->boolean('ORDRE_MISSION') ? 1 : 0,
            'CONVENTION' => 0,
            'EVAL_RISQUE' => 0,
            'CONVOCATIONS' => $request->boolean('CONVOCATIONS') ? 1 : 0,
        ]);

        return redirect()->route('admin.references.event-type')
            ->with('success', 'Type d\'événement créé.');
    }

    public function eventTypeUpdate(Request $request, string $code): RedirectResponse
    {
        $v = $request->validate([
            'TE_LIBELLE' => ['required', 'string', 'max:40'],
            'CEV_CODE' => ['required', 'string', 'max:5', 'exists:categorie_evenement,CEV_CODE'],
        ]);

        DB::table('type_evenement')->where('TE_CODE', $code)->update([
            'TE_LIBELLE' => $v['TE_LIBELLE'],
            'CEV_CODE' => $v['CEV_CODE'],
            'FICHE_PRESENCE' => $request->boolean('FICHE_PRESENCE') ? 1 : 0,
            'ORDRE_MISSION' => $request->boolean('ORDRE_MISSION') ? 1 : 0,
            'CONVOCATIONS' => $request->boolean('CONVOCATIONS') ? 1 : 0,
        ]);

        return redirect()->route('admin.references.event-type')
            ->with('success', 'Type d\'événement mis à jour.');
    }

    public function eventTypeDestroy(string $code): RedirectResponse
    {
        $used = DB::table('evenement')->where('TE_CODE', $code)->exists();
        if ($used) {
            return redirect()->route('admin.references.event-type')
                ->with('error', 'Ce type est utilisé par des activités et ne peut pas être supprimé.');
        }

        DB::table('type_participation')->where('TE_CODE', $code)->delete();
        DB::table('type_evenement')->where('TE_CODE', $code)->delete();

        return redirect()->route('admin.references.event-type')
            ->with('success', 'Type d\'événement supprimé.');
    }

    // ── Type Participation ────────────────────────────────────────────────────

    public function participationTypeIndex(): View
    {
        $items = DB::table('type_participation as tp')
            ->join('type_evenement as te', 'tp.TE_CODE', '=', 'te.TE_CODE')
            ->orderBy('te.TE_LIBELLE')
            ->orderBy('tp.TP_NUM')
            ->select('tp.TP_ID', 'tp.TP_LIBELLE', 'tp.TP_NUM', 'tp.TE_CODE', 'te.TE_LIBELLE as te_label')
            ->get();

        $eventTypes = DB::table('type_evenement')->orderBy('TE_LIBELLE')->get(['TE_CODE', 'TE_LIBELLE']);

        return view('admin.references.participation-type', compact('items', 'eventTypes'));
    }

    public function participationTypeStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'TE_CODE' => ['required', 'string', 'max:5', 'exists:type_evenement,TE_CODE'],
            'TP_LIBELLE' => ['required', 'string', 'max:40'],
            'TP_NUM' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        DB::table('type_participation')->insert([
            'TE_CODE' => $v['TE_CODE'],
            'TP_LIBELLE' => $v['TP_LIBELLE'],
            'TP_NUM' => $v['TP_NUM'],
            'PS_ID' => 0,
            'PS_ID2' => 0,
            'INSTRUCTOR' => 0,
            'EQ_ID' => 0,
        ]);

        return redirect()->route('admin.references.participation-type')
            ->with('success', 'Fonction ajoutée.');
    }

    public function participationTypeUpdate(Request $request, int $id): RedirectResponse
    {
        $v = $request->validate([
            'TP_LIBELLE' => ['required', 'string', 'max:40'],
            'TP_NUM' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        DB::table('type_participation')->where('TP_ID', $id)->update([
            'TP_LIBELLE' => $v['TP_LIBELLE'],
            'TP_NUM' => $v['TP_NUM'],
        ]);

        return redirect()->route('admin.references.participation-type')
            ->with('success', 'Fonction mise à jour.');
    }

    public function participationTypeDestroy(int $id): RedirectResponse
    {
        DB::table('type_participation')->where('TP_ID', $id)->delete();

        return redirect()->route('admin.references.participation-type')
            ->with('success', 'Fonction supprimée.');
    }

    // ── Type Matériel ─────────────────────────────────────────────────────────

    public function equipmentTypeIndex(): View
    {
        $items = DB::table('type_materiel')
            ->orderBy('TM_USAGE')
            ->orderBy('TM_DESCRIPTION')
            ->get();

        return view('admin.references.equipment-type', compact('items'));
    }

    public function equipmentTypeStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'TM_CODE' => ['required', 'string', 'max:25'],
            'TM_DESCRIPTION' => ['required', 'string', 'max:60'],
            'TM_USAGE' => ['nullable', 'string', 'max:15'],
        ]);

        DB::table('type_materiel')->insert([
            'TM_CODE' => $v['TM_CODE'],
            'TM_DESCRIPTION' => $v['TM_DESCRIPTION'],
            'TM_USAGE' => $v['TM_USAGE'] ?: 'DIVERS',
            'TM_LOT' => 0,
        ]);

        return redirect()->route('admin.references.equipment-type')
            ->with('success', 'Type de matériel créé.');
    }

    public function equipmentTypeUpdate(Request $request, int $id): RedirectResponse
    {
        $v = $request->validate([
            'TM_CODE' => ['required', 'string', 'max:25'],
            'TM_DESCRIPTION' => ['required', 'string', 'max:60'],
            'TM_USAGE' => ['nullable', 'string', 'max:15'],
        ]);

        DB::table('type_materiel')->where('TM_ID', $id)->update([
            'TM_CODE' => $v['TM_CODE'],
            'TM_DESCRIPTION' => $v['TM_DESCRIPTION'],
            'TM_USAGE' => $v['TM_USAGE'] ?: 'DIVERS',
        ]);

        return redirect()->route('admin.references.equipment-type')
            ->with('success', 'Type de matériel mis à jour.');
    }

    public function equipmentTypeDestroy(int $id): RedirectResponse
    {
        $used = DB::table('materiel')->where('TM_ID', $id)->exists();
        if ($used) {
            return redirect()->route('admin.references.equipment-type')
                ->with('error', 'Ce type est utilisé et ne peut pas être supprimé.');
        }

        DB::table('type_materiel')->where('TM_ID', $id)->delete();

        return redirect()->route('admin.references.equipment-type')
            ->with('success', 'Type de matériel supprimé.');
    }

    // ── Type Consumable ──────────────────────────────────────────────────────

    public function consumableTypeIndex(): View
    {
        $items = DB::table('type_consommable')
            ->orderBy('CC_CODE')
            ->orderBy('TC_DESCRIPTION')
            ->get();

        return view('admin.references.consumable-type', compact('items'));
    }

    public function consumableTypeStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'TC_DESCRIPTION' => ['required', 'string', 'max:60'],
            'CC_CODE' => ['required', 'string', 'max:12'],
            'TC_CONDITIONNEMENT' => ['required', 'string', 'max:2'],
            'TC_UNITE_MESURE' => ['required', 'string', 'max:2'],
            'TC_QUANTITE_PAR_UNITE' => ['required', 'numeric', 'min:0'],
        ]);

        DB::table('type_consommable')->insert([
            'TC_DESCRIPTION' => $v['TC_DESCRIPTION'],
            'CC_CODE' => $v['CC_CODE'],
            'TC_CONDITIONNEMENT' => $v['TC_CONDITIONNEMENT'],
            'TC_UNITE_MESURE' => $v['TC_UNITE_MESURE'],
            'TC_QUANTITE_PAR_UNITE' => $v['TC_QUANTITE_PAR_UNITE'],
            'TC_PEREMPTION' => $request->boolean('TC_PEREMPTION') ? 1 : 0,
        ]);

        return redirect()->route('admin.references.consumable-type')
            ->with('success', 'Type de consommable créé.');
    }

    public function consumableTypeUpdate(Request $request, int $id): RedirectResponse
    {
        $v = $request->validate([
            'TC_DESCRIPTION' => ['required', 'string', 'max:60'],
            'CC_CODE' => ['required', 'string', 'max:12'],
            'TC_CONDITIONNEMENT' => ['required', 'string', 'max:2'],
            'TC_UNITE_MESURE' => ['required', 'string', 'max:2'],
            'TC_QUANTITE_PAR_UNITE' => ['required', 'numeric', 'min:0'],
        ]);

        DB::table('type_consommable')->where('TC_ID', $id)->update([
            'TC_DESCRIPTION' => $v['TC_DESCRIPTION'],
            'CC_CODE' => $v['CC_CODE'],
            'TC_CONDITIONNEMENT' => $v['TC_CONDITIONNEMENT'],
            'TC_UNITE_MESURE' => $v['TC_UNITE_MESURE'],
            'TC_QUANTITE_PAR_UNITE' => $v['TC_QUANTITE_PAR_UNITE'],
            'TC_PEREMPTION' => $request->boolean('TC_PEREMPTION') ? 1 : 0,
        ]);

        return redirect()->route('admin.references.consumable-type')
            ->with('success', 'Type de consommable mis à jour.');
    }

    public function consumableTypeDestroy(int $id): RedirectResponse
    {
        $used = DB::table('consommable')->where('TC_ID', $id)->exists();
        if ($used) {
            return redirect()->route('admin.references.consumable-type')
                ->with('error', 'Ce type est utilisé et ne peut pas être supprimé.');
        }

        DB::table('type_consommable')->where('TC_ID', $id)->delete();

        return redirect()->route('admin.references.consumable-type')
            ->with('success', 'Type de consommable supprimé.');
    }

    // ── Véhicule types ────────────────────────────────────────────────────────

    public function vehicleTypeIndex(): View
    {
        $items = DB::table('type_vehicule')
            ->orderBy('TV_USAGE')
            ->orderBy('TV_CODE')
            ->get();

        return view('admin.references.vehicle-type', compact('items'));
    }

    public function vehicleTypeStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'TV_CODE' => ['required', 'string', 'max:10', 'unique:type_vehicule,TV_CODE'],
            'TV_LIBELLE' => ['required', 'string', 'max:60'],
            'TV_USAGE' => ['required', 'string', 'max:12'],
            'TV_NB' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::table('type_vehicule')->insert([
            'TV_CODE' => strtoupper($v['TV_CODE']),
            'TV_LIBELLE' => $v['TV_LIBELLE'],
            'TV_USAGE' => $v['TV_USAGE'],
            'TV_NB' => $v['TV_NB'] ?? 0,
        ]);

        return redirect()->route('admin.references.vehicle-type')
            ->with('success', 'Type de véhicule créé.');
    }

    public function vehicleTypeUpdate(Request $request, string $code): RedirectResponse
    {
        $v = $request->validate([
            'TV_LIBELLE' => ['required', 'string', 'max:60'],
            'TV_USAGE' => ['required', 'string', 'max:12'],
            'TV_NB' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::table('type_vehicule')->where('TV_CODE', $code)->update([
            'TV_LIBELLE' => $v['TV_LIBELLE'],
            'TV_USAGE' => $v['TV_USAGE'],
            'TV_NB' => $v['TV_NB'] ?? 0,
        ]);

        return redirect()->route('admin.references.vehicle-type')
            ->with('success', 'Type de véhicule mis à jour.');
    }

    public function vehicleTypeDestroy(string $code): RedirectResponse
    {
        $used = DB::table('vehicule')->where('TV_CODE', $code)->exists();
        if ($used) {
            return redirect()->route('admin.references.vehicle-type')
                ->with('error', 'Ce type est utilisé par des véhicules et ne peut pas être supprimé.');
        }

        DB::table('type_vehicule')->where('TV_CODE', $code)->delete();

        return redirect()->route('admin.references.vehicle-type')
            ->with('success', 'Type de véhicule supprimé.');
    }

    // ── Grade icons ───────────────────────────────────────────────────────────

    public function gradeIndex(): View
    {
        $grades = DB::table('grade as g')
            ->leftJoin('categorie_grade as cg', 'cg.CG_CODE', '=', 'g.G_CATEGORY')
            ->orderBy('g.G_CATEGORY')
            ->orderBy('g.G_LEVEL')
            ->select('g.G_GRADE', 'g.G_DESCRIPTION', 'g.G_LEVEL', 'g.G_ICON',
                'g.G_CATEGORY', 'cg.CG_DESCRIPTION as cat_label')
            ->get();

        return view('admin.references.grade', compact('grades'));
    }

    public function gradeIconUpload(Request $request, string $grade): RedirectResponse
    {
        $row = DB::table('grade')->where('G_GRADE', $grade)->first();
        abort_if($row === null, 404);

        $request->validate([
            'icon' => ['required', 'file', 'mimes:png,jpg,jpeg,gif,webp', 'max:512'],
        ]);

        // Remove old icon from storage if it's in our managed path
        $old = $row->G_ICON ?? '';
        if ($old && str_starts_with($old, 'grades/') && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('icon')->storeAs(
            'grades',
            strtoupper($grade).'.'.$request->file('icon')->extension(),
            'public'
        );

        DB::table('grade')->where('G_GRADE', $grade)->update(['G_ICON' => $path]);

        return redirect()->route('admin.references.grade')
            ->with('success', "Icône mise à jour pour {$grade}.");
    }

    public function gradeIconDestroy(string $grade): RedirectResponse
    {
        $row = DB::table('grade')->where('G_GRADE', $grade)->first();
        abort_if($row === null, 404);

        $path = $row->G_ICON ?? '';
        if ($path && str_starts_with($path, 'grades/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        DB::table('grade')->where('G_GRADE', $grade)->update(['G_ICON' => null]);

        return redirect()->route('admin.references.grade')
            ->with('success', "Icône supprimée pour {$grade}.");
    }
}
