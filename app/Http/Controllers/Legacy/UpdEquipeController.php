<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Equipe;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_equipe.php
 * Legacy pattern: edit
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdEquipeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_equipe.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Equipe::findOrFail($id);

        return view('legacy_migrated.upd_equipe.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'EQ_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'EQ_NOM' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'EQ_ORDER' => 'nullable|string|max:255',
        ]);

        $item = Equipe::create([
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'EQ_NOM' => $validated['EQ_NOM'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'EQ_ORDER' => $validated['EQ_ORDER'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_equipe.edit', $item->id)
            ->with('success', 'Equipe created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Equipe::findOrFail($id);

        $validated = $request->validate([
            'EQ_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'EQ_NOM' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'EQ_ORDER' => 'nullable|string|max:255',
        ]);

        $item->update([
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'EQ_NOM' => $validated['EQ_NOM'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'EQ_ORDER' => $validated['EQ_ORDER'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_equipe.edit', $item->id)
            ->with('success', 'Equipe updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Equipe::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_equipe.index')
            ->with('success', 'Equipe deleted successfully');
    }
                
}
