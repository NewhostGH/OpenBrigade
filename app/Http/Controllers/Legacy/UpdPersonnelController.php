<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_personnel.php
 * Legacy pattern: edit
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdPersonnelController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_personnel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Personnel::findOrFail($id);

        return view('legacy_migrated.upd_personnel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'activite' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'upload' => 'nullable|file',
            'humainAnimal' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'no_prenom' => 'nullable|string|max:255',
            'prenom2' => 'nullable|string|max:255',
            'nom_naissance' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'habilitation' => 'nullable|string|max:255',
            'habilitation2' => 'nullable|string|max:255',
            'birth' => 'nullable|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'birthdep' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
        ]);

        $item = Personnel::create([
            'P_ID' => $validated['P_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'activite' => $validated['activite'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'upload' => $validated['upload'] ?? null,
            'humainAnimal' => $validated['humainAnimal'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'prenom' => $validated['prenom'] ?? null,
            'no_prenom' => $validated['no_prenom'] ?? null,
            'prenom2' => $validated['prenom2'] ?? null,
            'nom_naissance' => $validated['nom_naissance'] ?? null,
            'matricule' => $validated['matricule'] ?? null,
            'company' => $validated['company'] ?? null,
            'habilitation' => $validated['habilitation'] ?? null,
            'habilitation2' => $validated['habilitation2'] ?? null,
            'birth' => $validated['birth'] ?? null,
            'birthplace' => $validated['birthplace'] ?? null,
            'birthdep' => $validated['birthdep'] ?? null,
            'pays' => $validated['pays'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_personnel.edit', $item->id)
            ->with('success', 'Personnel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Personnel::findOrFail($id);

        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'activite' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'upload' => 'nullable|file',
            'humainAnimal' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'no_prenom' => 'nullable|string|max:255',
            'prenom2' => 'nullable|string|max:255',
            'nom_naissance' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'habilitation' => 'nullable|string|max:255',
            'habilitation2' => 'nullable|string|max:255',
            'birth' => 'nullable|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'birthdep' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
        ]);

        $item->update([
            'P_ID' => $validated['P_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'activite' => $validated['activite'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'upload' => $validated['upload'] ?? null,
            'humainAnimal' => $validated['humainAnimal'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'prenom' => $validated['prenom'] ?? null,
            'no_prenom' => $validated['no_prenom'] ?? null,
            'prenom2' => $validated['prenom2'] ?? null,
            'nom_naissance' => $validated['nom_naissance'] ?? null,
            'matricule' => $validated['matricule'] ?? null,
            'company' => $validated['company'] ?? null,
            'habilitation' => $validated['habilitation'] ?? null,
            'habilitation2' => $validated['habilitation2'] ?? null,
            'birth' => $validated['birth'] ?? null,
            'birthplace' => $validated['birthplace'] ?? null,
            'birthdep' => $validated['birthdep'] ?? null,
            'pays' => $validated['pays'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_personnel.edit', $item->id)
            ->with('success', 'Personnel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Personnel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_personnel.index')
            ->with('success', 'Personnel deleted successfully');
    }
                
}
