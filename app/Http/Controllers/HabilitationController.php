<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HabilitationController extends Controller
{
    public function index(): View
    {
        $groups = DB::table('groupe')
            ->where('TR_CONFIG', 1)
            ->orderBy('GP_ORDER')
            ->orderBy('GP_ID')
            ->get();

        $features = DB::table('fonctionnalite as f')
            ->leftJoin('type_fonctionnalite as tf', 'tf.TF_ID', '=', 'f.TF_ID')
            ->orderBy('f.TF_ID')
            ->orderBy('f.F_ID')
            ->select('f.F_ID', 'f.F_LIBELLE', 'f.F_DESCRIPTION', 'f.F_TYPE', 'f.F_FLAG',
                     'tf.TF_DESCRIPTION as category')
            ->get();

        // Build a set: "GP_ID|F_ID" for quick lookup
        $granted = DB::table('habilitation')
            ->whereIn('GP_ID', $groups->pluck('GP_ID'))
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->GP_ID}|{$r->F_ID}" => true]);

        $featuresByCategory = $features->groupBy('category');

        return view('admin.habilitations.index', compact('groups', 'featuresByCategory', 'granted'));
    }

    public function toggle(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'GP_ID' => ['required', 'integer'],
            'F_ID'  => ['required', 'integer'],
            'grant' => ['required', 'boolean'],
        ]);

        $gpId = (int) $v['GP_ID'];
        $fId  = (int) $v['F_ID'];

        // Protect: GP_ID=4 (admin) always keeps F_ID=0 (login) and F_ID=9 (security)
        if ($gpId === 4 && in_array($fId, [0, 9]) && ! $v['grant']) {
            return redirect()->route('admin.habilitations')
                ->with('error', 'Cette permission est protégée pour le groupe admin.');
        }

        if ($v['grant']) {
            DB::table('habilitation')->insertOrIgnore(['GP_ID' => $gpId, 'F_ID' => $fId]);
        } else {
            DB::table('habilitation')->where('GP_ID', $gpId)->where('F_ID', $fId)->delete();
        }

        return redirect()->route('admin.habilitations')
            ->with('success', 'Habilitation mise à jour.');
    }

    public function groupStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'GP_ID'          => ['required', 'integer', 'unique:groupe,GP_ID'],
            'GP_DESCRIPTION' => ['required', 'string', 'max:30'],
            'GP_USAGE'       => ['required', 'in:internes,externes,all'],
        ]);

        DB::table('groupe')->insert([
            'GP_ID'           => $v['GP_ID'],
            'GP_DESCRIPTION'  => $v['GP_DESCRIPTION'],
            'TR_CONFIG'       => 1,
            'TR_SUB_POSSIBLE' => 0,
            'TR_ALL_POSSIBLE' => 0,
            'TR_WIDGET'       => 0,
            'GP_USAGE'        => $v['GP_USAGE'],
            'GP_ASTREINTE'    => 0,
            'GP_ORDER'        => 50,
        ]);

        // Always grant F_ID=0 (login) to new groups
        DB::table('habilitation')->insertOrIgnore(['GP_ID' => $v['GP_ID'], 'F_ID' => 0]);

        return redirect()->route('admin.habilitations')
            ->with('success', "Groupe « {$v['GP_DESCRIPTION']} » créé.");
    }

    public function groupUpdate(Request $request, int $gpId): RedirectResponse
    {
        $v = $request->validate([
            'GP_DESCRIPTION' => ['required', 'string', 'max:30'],
            'GP_USAGE'       => ['required', 'in:internes,externes,all'],
            'GP_ORDER'       => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        DB::table('groupe')->where('GP_ID', $gpId)->update([
            'GP_DESCRIPTION' => $v['GP_DESCRIPTION'],
            'GP_USAGE'       => $v['GP_USAGE'],
            'GP_ORDER'       => $v['GP_ORDER'],
        ]);

        return redirect()->route('admin.habilitations')
            ->with('success', 'Groupe mis à jour.');
    }

    public function groupDestroy(int $gpId): RedirectResponse
    {
        // Protect system groups
        if (in_array($gpId, [-1, 0, 4])) {
            return redirect()->route('admin.habilitations')
                ->with('error', 'Ce groupe système ne peut pas être supprimé.');
        }

        $inUse = DB::table('pompier')
            ->where('GP_ID', $gpId)
            ->orWhere('GP_ID2', $gpId)
            ->exists();

        if ($inUse) {
            return redirect()->route('admin.habilitations')
                ->with('error', 'Ce groupe est affecté à du personnel et ne peut pas être supprimé.');
        }

        DB::table('habilitation')->where('GP_ID', $gpId)->delete();
        DB::table('groupe')->where('GP_ID', $gpId)->delete();

        return redirect()->route('admin.habilitations')
            ->with('success', 'Groupe supprimé.');
    }
}
