<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Habilitations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_habilitations.php
 * Legacy pattern: edit
 * Legacy permission id: 9
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdHabilitationsController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_habilitations.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Habilitations::findOrFail($id);

        return view('legacy_migrated.upd_habilitations.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'GP_ID' => 'nullable|string|max:255',
            'GP_DESCRIPTION' => 'nullable|string|max:255',
            'sub_possible' => 'nullable|string|max:255',
            'all_possible' => 'nullable|string|max:255',
            'gp_usage' => 'nullable|string|max:255',
            'gp_astreinte' => 'nullable|string|max:255',
            'gp_order' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'tr_widget' => 'nullable|string|max:255',
            '$F_ID' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item = Habilitations::create([
            'GP_ID' => $validated['GP_ID'] ?? null,
            'GP_DESCRIPTION' => $validated['GP_DESCRIPTION'] ?? null,
            'sub_possible' => $validated['sub_possible'] ?? null,
            'all_possible' => $validated['all_possible'] ?? null,
            'gp_usage' => $validated['gp_usage'] ?? null,
            'gp_astreinte' => $validated['gp_astreinte'] ?? null,
            'gp_order' => $validated['gp_order'] ?? null,
            'category' => $validated['category'] ?? null,
            'tr_widget' => $validated['tr_widget'] ?? null,
            '$F_ID' => $validated['$F_ID'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_habilitations.edit', $item->id)
            ->with('success', 'Habilitations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Habilitations::findOrFail($id);

        $validated = $request->validate([
            'GP_ID' => 'nullable|string|max:255',
            'GP_DESCRIPTION' => 'nullable|string|max:255',
            'sub_possible' => 'nullable|string|max:255',
            'all_possible' => 'nullable|string|max:255',
            'gp_usage' => 'nullable|string|max:255',
            'gp_astreinte' => 'nullable|string|max:255',
            'gp_order' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'tr_widget' => 'nullable|string|max:255',
            '$F_ID' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item->update([
            'GP_ID' => $validated['GP_ID'] ?? null,
            'GP_DESCRIPTION' => $validated['GP_DESCRIPTION'] ?? null,
            'sub_possible' => $validated['sub_possible'] ?? null,
            'all_possible' => $validated['all_possible'] ?? null,
            'gp_usage' => $validated['gp_usage'] ?? null,
            'gp_astreinte' => $validated['gp_astreinte'] ?? null,
            'gp_order' => $validated['gp_order'] ?? null,
            'category' => $validated['category'] ?? null,
            'tr_widget' => $validated['tr_widget'] ?? null,
            '$F_ID' => $validated['$F_ID'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_habilitations.edit', $item->id)
            ->with('success', 'Habilitations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Habilitations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_habilitations.index')
            ->with('success', 'Habilitations deleted successfully');
    }
                
}
