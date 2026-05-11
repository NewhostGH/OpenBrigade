<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DpsCalc;
use Illuminate\Http\Request;

/**
 * Legacy migration source: dps_calc.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class DpsCalcController extends Controller
{
    public function index(Request $request)
    {
        $query = DpsCalc::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.dps_calc.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.dps_calc.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DpsCalc::findOrFail($id);

        return view('legacy_migrated.dps_calc.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'P1' => 'nullable|string|max:255',
            'P2' => 'nullable|string|max:255',
            'E1' => 'nullable|string|max:255',
            'E2' => 'nullable|string|max:255',
            'dimNbISActeurs' => 'nullable|string|max:255',
            'dimNbISActeursCom' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'actionPrint' => 'nullable|string|max:255',
        ]);

        $item = DpsCalc::create([
            'P1' => $validated['P1'] ?? null,
            'P2' => $validated['P2'] ?? null,
            'E1' => $validated['E1'] ?? null,
            'E2' => $validated['E2'] ?? null,
            'dimNbISActeurs' => $validated['dimNbISActeurs'] ?? null,
            'dimNbISActeursCom' => $validated['dimNbISActeursCom'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'actionPrint' => $validated['actionPrint'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dps_calc.edit', $item->id)
            ->with('success', 'DpsCalc created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DpsCalc::findOrFail($id);

        $validated = $request->validate([
            'P1' => 'nullable|string|max:255',
            'P2' => 'nullable|string|max:255',
            'E1' => 'nullable|string|max:255',
            'E2' => 'nullable|string|max:255',
            'dimNbISActeurs' => 'nullable|string|max:255',
            'dimNbISActeursCom' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'actionPrint' => 'nullable|string|max:255',
        ]);

        $item->update([
            'P1' => $validated['P1'] ?? null,
            'P2' => $validated['P2'] ?? null,
            'E1' => $validated['E1'] ?? null,
            'E2' => $validated['E2'] ?? null,
            'dimNbISActeurs' => $validated['dimNbISActeurs'] ?? null,
            'dimNbISActeursCom' => $validated['dimNbISActeursCom'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'actionPrint' => $validated['actionPrint'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dps_calc.edit', $item->id)
            ->with('success', 'DpsCalc updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DpsCalc::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.dps_calc.index')
            ->with('success', 'DpsCalc deleted successfully');
    }
                
}
