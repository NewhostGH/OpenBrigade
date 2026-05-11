<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_configuration.php
 * Legacy pattern: save
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveConfigurationController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_configuration.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Configuration::findOrFail($id);

        return view('legacy_migrated.save_configuration.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
        ]);

        $item = Configuration::create([
            'tab' => $validated['tab'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_configuration.edit', $item->id)
            ->with('success', 'Configuration created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Configuration::findOrFail($id);

        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
        ]);

        $item->update([
            'tab' => $validated['tab'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_configuration.edit', $item->id)
            ->with('success', 'Configuration updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Configuration::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_configuration.index')
            ->with('success', 'Configuration deleted successfully');
    }
                
}
