<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_save.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.evenement_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Evenement::findOrFail($id);

        return view('legacy_migrated.evenement_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'copycheffrom' => 'nullable|string|max:255',
            'copydetailsfrom' => 'nullable|string|max:255',
            'copymode' => 'nullable|string|max:255',
            'closed' => 'nullable|string|max:255',
            'open_to_ext' => 'nullable|string|max:255',
            'allow_reinforcement' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'nb_vpsp' => 'nullable|string|max:255',
            'nb_autres_vehicules' => 'nullable|string|max:255',
            'canceled' => 'nullable|string|max:255',
            'flag1' => 'nullable|string|max:255',
            'colonne' => 'nullable|string|max:255',
            'visible_outside' => 'nullable|string|max:255',
            'mail1' => 'nullable|string|max:255',
            'mail2' => 'nullable|string|max:255',
            'mail3' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
        ]);

        $item = Evenement::create([
            'action' => $validated['action'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'copycheffrom' => $validated['copycheffrom'] ?? null,
            'copydetailsfrom' => $validated['copydetailsfrom'] ?? null,
            'copymode' => $validated['copymode'] ?? null,
            'closed' => $validated['closed'] ?? null,
            'open_to_ext' => $validated['open_to_ext'] ?? null,
            'allow_reinforcement' => $validated['allow_reinforcement'] ?? null,
            'section' => $validated['section'] ?? null,
            'nb_vpsp' => $validated['nb_vpsp'] ?? null,
            'nb_autres_vehicules' => $validated['nb_autres_vehicules'] ?? null,
            'canceled' => $validated['canceled'] ?? null,
            'flag1' => $validated['flag1'] ?? null,
            'colonne' => $validated['colonne'] ?? null,
            'visible_outside' => $validated['visible_outside'] ?? null,
            'mail1' => $validated['mail1'] ?? null,
            'mail2' => $validated['mail2'] ?? null,
            'mail3' => $validated['mail3'] ?? null,
            'company' => $validated['company'] ?? null,
            'contact_name' => $validated['contact_name'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_save.edit', $item->id)
            ->with('success', 'Evenement created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Evenement::findOrFail($id);

        $validated = $request->validate([
            'action' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'copycheffrom' => 'nullable|string|max:255',
            'copydetailsfrom' => 'nullable|string|max:255',
            'copymode' => 'nullable|string|max:255',
            'closed' => 'nullable|string|max:255',
            'open_to_ext' => 'nullable|string|max:255',
            'allow_reinforcement' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'nb_vpsp' => 'nullable|string|max:255',
            'nb_autres_vehicules' => 'nullable|string|max:255',
            'canceled' => 'nullable|string|max:255',
            'flag1' => 'nullable|string|max:255',
            'colonne' => 'nullable|string|max:255',
            'visible_outside' => 'nullable|string|max:255',
            'mail1' => 'nullable|string|max:255',
            'mail2' => 'nullable|string|max:255',
            'mail3' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
        ]);

        $item->update([
            'action' => $validated['action'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'copycheffrom' => $validated['copycheffrom'] ?? null,
            'copydetailsfrom' => $validated['copydetailsfrom'] ?? null,
            'copymode' => $validated['copymode'] ?? null,
            'closed' => $validated['closed'] ?? null,
            'open_to_ext' => $validated['open_to_ext'] ?? null,
            'allow_reinforcement' => $validated['allow_reinforcement'] ?? null,
            'section' => $validated['section'] ?? null,
            'nb_vpsp' => $validated['nb_vpsp'] ?? null,
            'nb_autres_vehicules' => $validated['nb_autres_vehicules'] ?? null,
            'canceled' => $validated['canceled'] ?? null,
            'flag1' => $validated['flag1'] ?? null,
            'colonne' => $validated['colonne'] ?? null,
            'visible_outside' => $validated['visible_outside'] ?? null,
            'mail1' => $validated['mail1'] ?? null,
            'mail2' => $validated['mail2'] ?? null,
            'mail3' => $validated['mail3'] ?? null,
            'company' => $validated['company'] ?? null,
            'contact_name' => $validated['contact_name'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_save.edit', $item->id)
            ->with('success', 'Evenement updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Evenement::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_save.index')
            ->with('success', 'Evenement deleted successfully');
    }
                
}
