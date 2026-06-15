<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DutyTypeController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $items = DB::table('type_garde as tg')
            ->join('section as s', 's.S_ID', '=', 'tg.S_ID')
            ->where('tg.S_ID', $sectionId)
            ->orderBy('tg.EQ_ORDER')
            ->orderBy('tg.EQ_NOM')
            ->select(
                'tg.EQ_ID', 'tg.EQ_NOM', 'tg.EQ_JOUR', 'tg.EQ_NUIT',
                'tg.EQ_DEBUT1', 'tg.EQ_FIN1', 'tg.EQ_DUREE1',
                'tg.EQ_DEBUT2', 'tg.EQ_FIN2', 'tg.EQ_DUREE2',
                'tg.EQ_PERSONNEL1', 'tg.EQ_PERSONNEL2',
                'tg.EQ_VEHICULES', 'tg.EQ_DEFAULT', 'tg.EQ_ORDER',
                'tg.EQ_LIEU', 'tg.EQ_ADDRESS', 's.S_CODE'
            )
            ->get();

        $nextOrder = $items->max('EQ_ORDER') + 1;

        return view('duty.types', compact('items', 'sectionId', 'nextOrder'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $v = $request->validate([
            'EQ_NOM' => ['required', 'string', 'max:60'],
            'EQ_JOUR' => ['boolean'],
            'EQ_NUIT' => ['boolean'],
            'EQ_DEBUT1' => ['nullable', 'date_format:H:i'],
            'EQ_FIN1' => ['nullable', 'date_format:H:i'],
            'EQ_DUREE1' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'EQ_DEBUT2' => ['nullable', 'date_format:H:i'],
            'EQ_FIN2' => ['nullable', 'date_format:H:i'],
            'EQ_DUREE2' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'EQ_PERSONNEL1' => ['nullable', 'integer', 'min:0', 'max:999'],
            'EQ_PERSONNEL2' => ['nullable', 'integer', 'min:0', 'max:999'],
            'EQ_VEHICULES' => ['boolean'],
            'EQ_DEFAULT' => ['boolean'],
            'EQ_LIEU' => ['nullable', 'string', 'max:100'],
            'EQ_ADDRESS' => ['nullable', 'string', 'max:255'],
            'EQ_ORDER' => ['nullable', 'integer', 'min:1'],
        ]);

        $nextId = (int) DB::table('type_garde')->max('EQ_ID') + 1;
        $nextOrder = (int) DB::table('type_garde')->where('S_ID', $sectionId)->max('EQ_ORDER') + 1;

        if ($v['EQ_DEFAULT'] ?? false) {
            DB::table('type_garde')->where('S_ID', $sectionId)->update(['EQ_DEFAULT' => 0]);
        }

        DB::table('type_garde')->insert([
            'EQ_ID' => $nextId,
            'S_ID' => $sectionId,
            'EQ_NOM' => $v['EQ_NOM'],
            'EQ_JOUR' => (int) ($v['EQ_JOUR'] ?? false),
            'EQ_NUIT' => (int) ($v['EQ_NUIT'] ?? false),
            'EQ_DEBUT1' => $v['EQ_DEBUT1'] ?? '07:30',
            'EQ_FIN1' => $v['EQ_FIN1'] ?? '19:30',
            'EQ_DUREE1' => $v['EQ_DUREE1'] ?? 12,
            'EQ_DEBUT2' => $v['EQ_DEBUT2'] ?? '19:30',
            'EQ_FIN2' => $v['EQ_FIN2'] ?? '07:30',
            'EQ_DUREE2' => $v['EQ_DUREE2'] ?? 12,
            'EQ_PERSONNEL1' => $v['EQ_PERSONNEL1'] ?? 0,
            'EQ_PERSONNEL2' => $v['EQ_PERSONNEL2'] ?? 0,
            'EQ_VEHICULES' => (int) ($v['EQ_VEHICULES'] ?? false),
            'EQ_SPP' => 0,
            'EQ_DEFAULT' => (int) ($v['EQ_DEFAULT'] ?? false),
            'EQ_ORDER' => $v['EQ_ORDER'] ?? $nextOrder,
            'EQ_LIEU' => $v['EQ_LIEU'] ?? '',
            'EQ_ADDRESS' => $v['EQ_ADDRESS'] ?? '',
            'EQ_ICON' => 'images/gardes/GAR.png',
            'EQ_REGIME_TRAVAIL' => 0,
            'ASSURE_PAR1' => 0,
            'ASSURE_PAR2' => 0,
            'ASSURE_PAR_DATE' => now(),
        ]);

        return redirect()->route('duty.types.index')
            ->with('success', 'Type de garde créé.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $type = DB::table('type_garde')->where('EQ_ID', $id)->where('S_ID', $sectionId)->firstOrFail();

        $v = $request->validate([
            'EQ_NOM' => ['required', 'string', 'max:60'],
            'EQ_JOUR' => ['boolean'],
            'EQ_NUIT' => ['boolean'],
            'EQ_DEBUT1' => ['nullable', 'date_format:H:i'],
            'EQ_FIN1' => ['nullable', 'date_format:H:i'],
            'EQ_DUREE1' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'EQ_DEBUT2' => ['nullable', 'date_format:H:i'],
            'EQ_FIN2' => ['nullable', 'date_format:H:i'],
            'EQ_DUREE2' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'EQ_PERSONNEL1' => ['nullable', 'integer', 'min:0', 'max:999'],
            'EQ_PERSONNEL2' => ['nullable', 'integer', 'min:0', 'max:999'],
            'EQ_VEHICULES' => ['boolean'],
            'EQ_DEFAULT' => ['boolean'],
            'EQ_LIEU' => ['nullable', 'string', 'max:100'],
            'EQ_ADDRESS' => ['nullable', 'string', 'max:255'],
            'EQ_ORDER' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($v['EQ_DEFAULT'] ?? false) {
            DB::table('type_garde')
                ->where('S_ID', $sectionId)
                ->where('EQ_ID', '!=', $id)
                ->update(['EQ_DEFAULT' => 0]);
        }

        DB::table('type_garde')->where('EQ_ID', $id)->update([
            'EQ_NOM' => $v['EQ_NOM'],
            'EQ_JOUR' => (int) ($v['EQ_JOUR'] ?? false),
            'EQ_NUIT' => (int) ($v['EQ_NUIT'] ?? false),
            'EQ_DEBUT1' => $v['EQ_DEBUT1'] ?? $type->EQ_DEBUT1,
            'EQ_FIN1' => $v['EQ_FIN1'] ?? $type->EQ_FIN1,
            'EQ_DUREE1' => $v['EQ_DUREE1'] ?? $type->EQ_DUREE1,
            'EQ_DEBUT2' => $v['EQ_DEBUT2'] ?? $type->EQ_DEBUT2,
            'EQ_FIN2' => $v['EQ_FIN2'] ?? $type->EQ_FIN2,
            'EQ_DUREE2' => $v['EQ_DUREE2'] ?? $type->EQ_DUREE2,
            'EQ_PERSONNEL1' => $v['EQ_PERSONNEL1'] ?? 0,
            'EQ_PERSONNEL2' => $v['EQ_PERSONNEL2'] ?? 0,
            'EQ_VEHICULES' => (int) ($v['EQ_VEHICULES'] ?? false),
            'EQ_DEFAULT' => (int) ($v['EQ_DEFAULT'] ?? false),
            'EQ_ORDER' => $v['EQ_ORDER'] ?? $type->EQ_ORDER,
            'EQ_LIEU' => $v['EQ_LIEU'] ?? '',
            'EQ_ADDRESS' => $v['EQ_ADDRESS'] ?? '',
        ]);

        return redirect()->route('duty.types.index')
            ->with('success', 'Type de garde mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        DB::table('type_garde')->where('EQ_ID', $id)->where('S_ID', $sectionId)->firstOrFail();

        $usedCount = DB::table('evenement')->where('E_EQUIPE', $id)->count();
        if ($usedCount > 0) {
            return redirect()->route('duty.types.index')
                ->with('error', "Ce type de garde est utilisé par {$usedCount} garde(s) et ne peut pas être supprimé.");
        }

        DB::table('type_garde')->where('EQ_ID', $id)->delete();

        return redirect()->route('duty.types.index')
            ->with('success', 'Type de garde supprimé.');
    }
}
