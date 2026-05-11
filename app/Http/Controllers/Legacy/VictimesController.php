<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Victimes;
use Illuminate\Http\Request;

/**
 * Legacy migration source: victimes.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class VictimesController extends Controller
{
    public function index(Request $request)
    {
        $query = Victimes::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('el_id', 'like', '%' . $term . '%');
                $query->orWhere('cav_responsable', 'like', '%' . $term . '%');
                $query->orWhere('cav_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.victimes.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.victimes.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Victimes::findOrFail($id);

        return view('legacy_migrated.victimes.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'regulated' => 'nullable|string|max:255',
            'date_in' => 'nullable|string|max:255',
            'time_in' => 'nullable|string|max:255',
            'date_out' => 'nullable|string|max:255',
            'time_out' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|string|max:255',
            'age' => 'nullable|string|max:255',
            'numerotation' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'detresse_vitale' => 'nullable|string|max:255',
            'soins' => 'nullable|string|max:255',
            'decede' => 'nullable|string|max:255',
            'malaise' => 'nullable|string|max:255',
            'traumatisme' => 'nullable|string|max:255',
            'medicalise' => 'nullable|string|max:255',
            'vetements' => 'nullable|string|max:255',
            'alimentation' => 'nullable|string|max:255',
            'information' => 'nullable|string|max:255',
            'refus' => 'nullable|string|max:255',
        ]);

        $item = Victimes::create([
            'regulated' => $validated['regulated'] ?? null,
            'date_in' => $validated['date_in'] ?? null,
            'time_in' => $validated['time_in'] ?? null,
            'date_out' => $validated['date_out'] ?? null,
            'time_out' => $validated['time_out'] ?? null,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'age' => $validated['age'] ?? null,
            'numerotation' => $validated['numerotation'] ?? null,
            'address' => $validated['address'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'detresse_vitale' => $validated['detresse_vitale'] ?? null,
            'soins' => $validated['soins'] ?? null,
            'decede' => $validated['decede'] ?? null,
            'malaise' => $validated['malaise'] ?? null,
            'traumatisme' => $validated['traumatisme'] ?? null,
            'medicalise' => $validated['medicalise'] ?? null,
            'vetements' => $validated['vetements'] ?? null,
            'alimentation' => $validated['alimentation'] ?? null,
            'information' => $validated['information'] ?? null,
            'refus' => $validated['refus'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.victimes.edit', $item->id)
            ->with('success', 'Victimes created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Victimes::findOrFail($id);

        $validated = $request->validate([
            'regulated' => 'nullable|string|max:255',
            'date_in' => 'nullable|string|max:255',
            'time_in' => 'nullable|string|max:255',
            'date_out' => 'nullable|string|max:255',
            'time_out' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|string|max:255',
            'age' => 'nullable|string|max:255',
            'numerotation' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'detresse_vitale' => 'nullable|string|max:255',
            'soins' => 'nullable|string|max:255',
            'decede' => 'nullable|string|max:255',
            'malaise' => 'nullable|string|max:255',
            'traumatisme' => 'nullable|string|max:255',
            'medicalise' => 'nullable|string|max:255',
            'vetements' => 'nullable|string|max:255',
            'alimentation' => 'nullable|string|max:255',
            'information' => 'nullable|string|max:255',
            'refus' => 'nullable|string|max:255',
        ]);

        $item->update([
            'regulated' => $validated['regulated'] ?? null,
            'date_in' => $validated['date_in'] ?? null,
            'time_in' => $validated['time_in'] ?? null,
            'date_out' => $validated['date_out'] ?? null,
            'time_out' => $validated['time_out'] ?? null,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'age' => $validated['age'] ?? null,
            'numerotation' => $validated['numerotation'] ?? null,
            'address' => $validated['address'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'detresse_vitale' => $validated['detresse_vitale'] ?? null,
            'soins' => $validated['soins'] ?? null,
            'decede' => $validated['decede'] ?? null,
            'malaise' => $validated['malaise'] ?? null,
            'traumatisme' => $validated['traumatisme'] ?? null,
            'medicalise' => $validated['medicalise'] ?? null,
            'vetements' => $validated['vetements'] ?? null,
            'alimentation' => $validated['alimentation'] ?? null,
            'information' => $validated['information'] ?? null,
            'refus' => $validated['refus'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.victimes.edit', $item->id)
            ->with('success', 'Victimes updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Victimes::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.victimes.index')
            ->with('success', 'Victimes deleted successfully');
    }
                
}
