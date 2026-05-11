<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_type_garde.php
 * Legacy pattern: delete
 * Legacy permission id: 5
 * This file stems from a legacy migration and requires functional verification.
 */
class DelTypeGardeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_type_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeGarde::findOrFail($id);

        return view('legacy_migrated.del_type_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = TypeGarde::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_type_garde.edit', $item->id)
            ->with('success', 'TypeGarde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeGarde::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_type_garde.edit', $item->id)
            ->with('success', 'TypeGarde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeGarde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_type_garde.index')
            ->with('success', 'TypeGarde deleted successfully');
    }
                
}
