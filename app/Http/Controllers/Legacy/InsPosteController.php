<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use Illuminate\Http\Request;

/**
 * Legacy migration source: ins_poste.php
 * Legacy pattern: create
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class InsPosteController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.ins_poste.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Poste::findOrFail($id);

        return view('legacy_migrated.ins_poste.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'TYPE' => 'nullable|string|max:255',
            'DESCRIPTION' => 'nullable|string|max:255',
            'PS_EXPIRABLE' => 'nullable|string|max:255',
            'PS_AUDIT' => 'nullable|string|max:255',
            'PS_DIPLOMA' => 'nullable|string|max:255',
            'PS_NUMERO' => 'nullable|string|max:255',
            'PS_SECOURISME' => 'nullable|string|max:255',
            'PS_NATIONAL' => 'nullable|string|max:255',
            'PS_FORMATION' => 'nullable|string|max:255',
            'PS_RECYCLE' => 'nullable|string|max:255',
            'PS_USER_MODIFIABLE' => 'nullable|string|max:255',
            'PS_PRINTABLE' => 'nullable|string|max:255',
            'PS_PRINT_IMAGE' => 'nullable|string|max:255',
            'F_ID' => 'nullable|string|max:255',
            'PH_CODE' => 'nullable|string|max:255',
            'PH_LEVEL' => 'nullable|string|max:255',
            'PS_ORDER' => 'nullable|string|max:255',
            'DAYS_WARNING' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item = Poste::create([
            'operation' => $validated['operation'] ?? null,
            'TYPE' => $validated['TYPE'] ?? null,
            'DESCRIPTION' => $validated['DESCRIPTION'] ?? null,
            'PS_EXPIRABLE' => $validated['PS_EXPIRABLE'] ?? null,
            'PS_AUDIT' => $validated['PS_AUDIT'] ?? null,
            'PS_DIPLOMA' => $validated['PS_DIPLOMA'] ?? null,
            'PS_NUMERO' => $validated['PS_NUMERO'] ?? null,
            'PS_SECOURISME' => $validated['PS_SECOURISME'] ?? null,
            'PS_NATIONAL' => $validated['PS_NATIONAL'] ?? null,
            'PS_FORMATION' => $validated['PS_FORMATION'] ?? null,
            'PS_RECYCLE' => $validated['PS_RECYCLE'] ?? null,
            'PS_USER_MODIFIABLE' => $validated['PS_USER_MODIFIABLE'] ?? null,
            'PS_PRINTABLE' => $validated['PS_PRINTABLE'] ?? null,
            'PS_PRINT_IMAGE' => $validated['PS_PRINT_IMAGE'] ?? null,
            'F_ID' => $validated['F_ID'] ?? null,
            'PH_CODE' => $validated['PH_CODE'] ?? null,
            'PH_LEVEL' => $validated['PH_LEVEL'] ?? null,
            'PS_ORDER' => $validated['PS_ORDER'] ?? null,
            'DAYS_WARNING' => $validated['DAYS_WARNING'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_poste.edit', $item->id)
            ->with('success', 'Poste created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Poste::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'TYPE' => 'nullable|string|max:255',
            'DESCRIPTION' => 'nullable|string|max:255',
            'PS_EXPIRABLE' => 'nullable|string|max:255',
            'PS_AUDIT' => 'nullable|string|max:255',
            'PS_DIPLOMA' => 'nullable|string|max:255',
            'PS_NUMERO' => 'nullable|string|max:255',
            'PS_SECOURISME' => 'nullable|string|max:255',
            'PS_NATIONAL' => 'nullable|string|max:255',
            'PS_FORMATION' => 'nullable|string|max:255',
            'PS_RECYCLE' => 'nullable|string|max:255',
            'PS_USER_MODIFIABLE' => 'nullable|string|max:255',
            'PS_PRINTABLE' => 'nullable|string|max:255',
            'PS_PRINT_IMAGE' => 'nullable|string|max:255',
            'F_ID' => 'nullable|string|max:255',
            'PH_CODE' => 'nullable|string|max:255',
            'PH_LEVEL' => 'nullable|string|max:255',
            'PS_ORDER' => 'nullable|string|max:255',
            'DAYS_WARNING' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'TYPE' => $validated['TYPE'] ?? null,
            'DESCRIPTION' => $validated['DESCRIPTION'] ?? null,
            'PS_EXPIRABLE' => $validated['PS_EXPIRABLE'] ?? null,
            'PS_AUDIT' => $validated['PS_AUDIT'] ?? null,
            'PS_DIPLOMA' => $validated['PS_DIPLOMA'] ?? null,
            'PS_NUMERO' => $validated['PS_NUMERO'] ?? null,
            'PS_SECOURISME' => $validated['PS_SECOURISME'] ?? null,
            'PS_NATIONAL' => $validated['PS_NATIONAL'] ?? null,
            'PS_FORMATION' => $validated['PS_FORMATION'] ?? null,
            'PS_RECYCLE' => $validated['PS_RECYCLE'] ?? null,
            'PS_USER_MODIFIABLE' => $validated['PS_USER_MODIFIABLE'] ?? null,
            'PS_PRINTABLE' => $validated['PS_PRINTABLE'] ?? null,
            'PS_PRINT_IMAGE' => $validated['PS_PRINT_IMAGE'] ?? null,
            'F_ID' => $validated['F_ID'] ?? null,
            'PH_CODE' => $validated['PH_CODE'] ?? null,
            'PH_LEVEL' => $validated['PH_LEVEL'] ?? null,
            'PS_ORDER' => $validated['PS_ORDER'] ?? null,
            'DAYS_WARNING' => $validated['DAYS_WARNING'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_poste.edit', $item->id)
            ->with('success', 'Poste updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Poste::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.ins_poste.index')
            ->with('success', 'Poste deleted successfully');
    }
                
}
