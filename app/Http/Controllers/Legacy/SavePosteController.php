<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_poste.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePosteController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_poste.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Poste::findOrFail($id);

        return view('legacy_migrated.save_poste.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'PS_ID' => 'nullable|string|max:255',
            'PS_ORDER' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TYPE' => 'nullable|string|max:255',
            'DESCRIPTION' => 'nullable|string|max:255',
            'PS_EXPIRABLE' => 'nullable|string|max:255',
            'PS_AUDIT' => 'nullable|string|max:255',
            'PS_DIPLOMA' => 'nullable|string|max:255',
            'PS_NUMERO' => 'nullable|string|max:255',
            'PS_FORMATION' => 'nullable|string|max:255',
            'PS_SECOURISME' => 'nullable|string|max:255',
            'PS_NATIONAL' => 'nullable|string|max:255',
            'PS_PRINTABLE' => 'nullable|string|max:255',
            'PS_PRINT_IMAGE' => 'nullable|string|max:255',
            'PS_RECYCLE' => 'nullable|string|max:255',
            'PS_USER_MODIFIABLE' => 'nullable|string|max:255',
            'EQ_ID' => 'nullable|string|max:255',
            'F_ID' => 'nullable|string|max:255',
            'PH_CODE' => 'nullable|string|max:255',
            'DAYS_WARNING' => 'nullable|string|max:255',
        ]);

        $item = Poste::create([
            'PS_ID' => $validated['PS_ID'] ?? null,
            'PS_ORDER' => $validated['PS_ORDER'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TYPE' => $validated['TYPE'] ?? null,
            'DESCRIPTION' => $validated['DESCRIPTION'] ?? null,
            'PS_EXPIRABLE' => $validated['PS_EXPIRABLE'] ?? null,
            'PS_AUDIT' => $validated['PS_AUDIT'] ?? null,
            'PS_DIPLOMA' => $validated['PS_DIPLOMA'] ?? null,
            'PS_NUMERO' => $validated['PS_NUMERO'] ?? null,
            'PS_FORMATION' => $validated['PS_FORMATION'] ?? null,
            'PS_SECOURISME' => $validated['PS_SECOURISME'] ?? null,
            'PS_NATIONAL' => $validated['PS_NATIONAL'] ?? null,
            'PS_PRINTABLE' => $validated['PS_PRINTABLE'] ?? null,
            'PS_PRINT_IMAGE' => $validated['PS_PRINT_IMAGE'] ?? null,
            'PS_RECYCLE' => $validated['PS_RECYCLE'] ?? null,
            'PS_USER_MODIFIABLE' => $validated['PS_USER_MODIFIABLE'] ?? null,
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'F_ID' => $validated['F_ID'] ?? null,
            'PH_CODE' => $validated['PH_CODE'] ?? null,
            'DAYS_WARNING' => $validated['DAYS_WARNING'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_poste.edit', $item->id)
            ->with('success', 'Poste created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Poste::findOrFail($id);

        $validated = $request->validate([
            'PS_ID' => 'nullable|string|max:255',
            'PS_ORDER' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TYPE' => 'nullable|string|max:255',
            'DESCRIPTION' => 'nullable|string|max:255',
            'PS_EXPIRABLE' => 'nullable|string|max:255',
            'PS_AUDIT' => 'nullable|string|max:255',
            'PS_DIPLOMA' => 'nullable|string|max:255',
            'PS_NUMERO' => 'nullable|string|max:255',
            'PS_FORMATION' => 'nullable|string|max:255',
            'PS_SECOURISME' => 'nullable|string|max:255',
            'PS_NATIONAL' => 'nullable|string|max:255',
            'PS_PRINTABLE' => 'nullable|string|max:255',
            'PS_PRINT_IMAGE' => 'nullable|string|max:255',
            'PS_RECYCLE' => 'nullable|string|max:255',
            'PS_USER_MODIFIABLE' => 'nullable|string|max:255',
            'EQ_ID' => 'nullable|string|max:255',
            'F_ID' => 'nullable|string|max:255',
            'PH_CODE' => 'nullable|string|max:255',
            'DAYS_WARNING' => 'nullable|string|max:255',
        ]);

        $item->update([
            'PS_ID' => $validated['PS_ID'] ?? null,
            'PS_ORDER' => $validated['PS_ORDER'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TYPE' => $validated['TYPE'] ?? null,
            'DESCRIPTION' => $validated['DESCRIPTION'] ?? null,
            'PS_EXPIRABLE' => $validated['PS_EXPIRABLE'] ?? null,
            'PS_AUDIT' => $validated['PS_AUDIT'] ?? null,
            'PS_DIPLOMA' => $validated['PS_DIPLOMA'] ?? null,
            'PS_NUMERO' => $validated['PS_NUMERO'] ?? null,
            'PS_FORMATION' => $validated['PS_FORMATION'] ?? null,
            'PS_SECOURISME' => $validated['PS_SECOURISME'] ?? null,
            'PS_NATIONAL' => $validated['PS_NATIONAL'] ?? null,
            'PS_PRINTABLE' => $validated['PS_PRINTABLE'] ?? null,
            'PS_PRINT_IMAGE' => $validated['PS_PRINT_IMAGE'] ?? null,
            'PS_RECYCLE' => $validated['PS_RECYCLE'] ?? null,
            'PS_USER_MODIFIABLE' => $validated['PS_USER_MODIFIABLE'] ?? null,
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'F_ID' => $validated['F_ID'] ?? null,
            'PH_CODE' => $validated['PH_CODE'] ?? null,
            'DAYS_WARNING' => $validated['DAYS_WARNING'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_poste.edit', $item->id)
            ->with('success', 'Poste updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Poste::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_poste.index')
            ->with('success', 'Poste deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'delete') {
            return response()->json(['status' => 'ok', 'operation' => 'delete']);
        }

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
