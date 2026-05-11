<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\IcalcreatorClass;
use Illuminate\Http\Request;

/**
 * Legacy migration source: iCalcreator.class.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class IcalcreatorClassController extends Controller
{
    public function index(Request $request)
    {
        $query = IcalcreatorClass::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('components', 'like', '%' . $term . '%');
                $query->orWhere('componentsthatoccurswithinperiodfalseonlycomponentsthatstartswithinperiodparamboolsplitoptional', 'like', '%' . $term . '%');
                $query->orWhere('truedefaultonecomponentcopyeverydayitoccursduringtheperiodimpliesflatfalsefalseoneoccuranceofcomponentonlyinoutputarrayreturnarrayorfalsefunctionselectcomponentsstartyfalse', 'like', '%' . $term . '%');
                $query->orWhere('startmfalse', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.iCalcreator_class.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.iCalcreator_class.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = IcalcreatorClass::findOrFail($id);

        return view('legacy_migrated.iCalcreator_class.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = IcalcreatorClass::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.iCalcreator_class.edit', $item->id)
            ->with('success', 'IcalcreatorClass created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = IcalcreatorClass::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.iCalcreator_class.edit', $item->id)
            ->with('success', 'IcalcreatorClass updated successfully');
    }
                

    public function destroy($id)
    {
        $item = IcalcreatorClass::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.iCalcreator_class.index')
            ->with('success', 'IcalcreatorClass deleted successfully');
    }
                
}
