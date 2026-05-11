<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: ins_personnel.php
 * Legacy pattern: create
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class InsPersonnelController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.ins_personnel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Personnel::findOrFail($id);

        return view('legacy_migrated.ins_personnel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'habilitation' => 'nullable|string|max:255',
            'habilitation2' => 'nullable|string|max:255',
            'old_member' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'humainAnimal' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'statut' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'debut' => 'nullable|string|max:255',
            'birth' => 'nullable|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'birthdep' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $item = Personnel::create([
            'P_ID' => $validated['P_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'habilitation' => $validated['habilitation'] ?? null,
            'habilitation2' => $validated['habilitation2'] ?? null,
            'old_member' => $validated['old_member'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'humainAnimal' => $validated['humainAnimal'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'prenom' => $validated['prenom'] ?? null,
            'matricule' => $validated['matricule'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'company' => $validated['company'] ?? null,
            'debut' => $validated['debut'] ?? null,
            'birth' => $validated['birth'] ?? null,
            'birthplace' => $validated['birthplace'] ?? null,
            'birthdep' => $validated['birthdep'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_personnel.edit', $item->id)
            ->with('success', 'Personnel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Personnel::findOrFail($id);

        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'habilitation' => 'nullable|string|max:255',
            'habilitation2' => 'nullable|string|max:255',
            'old_member' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'humainAnimal' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'statut' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'debut' => 'nullable|string|max:255',
            'birth' => 'nullable|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'birthdep' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $item->update([
            'P_ID' => $validated['P_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'habilitation' => $validated['habilitation'] ?? null,
            'habilitation2' => $validated['habilitation2'] ?? null,
            'old_member' => $validated['old_member'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'humainAnimal' => $validated['humainAnimal'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'prenom' => $validated['prenom'] ?? null,
            'matricule' => $validated['matricule'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'company' => $validated['company'] ?? null,
            'debut' => $validated['debut'] ?? null,
            'birth' => $validated['birth'] ?? null,
            'birthplace' => $validated['birthplace'] ?? null,
            'birthdep' => $validated['birthdep'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_personnel.edit', $item->id)
            ->with('success', 'Personnel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Personnel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.ins_personnel.index')
            ->with('success', 'Personnel deleted successfully');
    }
                
}
