<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Password;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_password.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePasswordController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_password.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Password::findOrFail($id);

        return view('legacy_migrated.save_password.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'new1' => 'nullable|string|max:255',
            'new2' => 'nullable|string|max:255',
            'current' => 'nullable|string|max:255',
        ]);

        $item = Password::create([
            'new1' => $validated['new1'] ?? null,
            'new2' => $validated['new2'] ?? null,
            'current' => $validated['current'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_password.edit', $item->id)
            ->with('success', 'Password created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Password::findOrFail($id);

        $validated = $request->validate([
            'new1' => 'nullable|string|max:255',
            'new2' => 'nullable|string|max:255',
            'current' => 'nullable|string|max:255',
        ]);

        $item->update([
            'new1' => $validated['new1'] ?? null,
            'new2' => $validated['new2'] ?? null,
            'current' => $validated['current'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_password.edit', $item->id)
            ->with('success', 'Password updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Password::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_password.index')
            ->with('success', 'Password deleted successfully');
    }
                
}
