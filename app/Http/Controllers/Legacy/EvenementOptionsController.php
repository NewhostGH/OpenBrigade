<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementOptions;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_options.php
 * Legacy pattern: list
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementOptionsController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementOptions::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_code', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('e_closed', 'like', '%' . $term . '%');
                $query->orWhere('e_canceled', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_options.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_options.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementOptions::findOrFail($id);

        return view('legacy_migrated.evenement_options.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'choix_texte_' => 'nullable|string|max:255',
            'newtexte' => 'nullable|string|max:255',
            'choix_value_' => 'nullable|string|max:255',
            'newvalue' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'option' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'EO_TITLE' => 'nullable|string|max:255',
            'EOG_TITLE' => 'nullable|string|max:255',
            'EO_TYPE' => 'nullable|string|max:255',
            'EO_COMMENT' => 'nullable|string|max:255',
            'EO_ORDER' => 'nullable|string|max:255',
            'EOG_ORDER' => 'nullable|string|max:255',
        ]);

        $item = EvenementOptions::create([
            'choix_texte_' => $validated['choix_texte_'] ?? null,
            'newtexte' => $validated['newtexte'] ?? null,
            'choix_value_' => $validated['choix_value_'] ?? null,
            'newvalue' => $validated['newvalue'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'option' => $validated['option'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'action' => $validated['action'] ?? null,
            'EO_TITLE' => $validated['EO_TITLE'] ?? null,
            'EOG_TITLE' => $validated['EOG_TITLE'] ?? null,
            'EO_TYPE' => $validated['EO_TYPE'] ?? null,
            'EO_COMMENT' => $validated['EO_COMMENT'] ?? null,
            'EO_ORDER' => $validated['EO_ORDER'] ?? null,
            'EOG_ORDER' => $validated['EOG_ORDER'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_options.edit', $item->id)
            ->with('success', 'EvenementOptions created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementOptions::findOrFail($id);

        $validated = $request->validate([
            'choix_texte_' => 'nullable|string|max:255',
            'newtexte' => 'nullable|string|max:255',
            'choix_value_' => 'nullable|string|max:255',
            'newvalue' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'option' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'EO_TITLE' => 'nullable|string|max:255',
            'EOG_TITLE' => 'nullable|string|max:255',
            'EO_TYPE' => 'nullable|string|max:255',
            'EO_COMMENT' => 'nullable|string|max:255',
            'EO_ORDER' => 'nullable|string|max:255',
            'EOG_ORDER' => 'nullable|string|max:255',
        ]);

        $item->update([
            'choix_texte_' => $validated['choix_texte_'] ?? null,
            'newtexte' => $validated['newtexte'] ?? null,
            'choix_value_' => $validated['choix_value_'] ?? null,
            'newvalue' => $validated['newvalue'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'option' => $validated['option'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'action' => $validated['action'] ?? null,
            'EO_TITLE' => $validated['EO_TITLE'] ?? null,
            'EOG_TITLE' => $validated['EOG_TITLE'] ?? null,
            'EO_TYPE' => $validated['EO_TYPE'] ?? null,
            'EO_COMMENT' => $validated['EO_COMMENT'] ?? null,
            'EO_ORDER' => $validated['EO_ORDER'] ?? null,
            'EOG_ORDER' => $validated['EOG_ORDER'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_options.edit', $item->id)
            ->with('success', 'EvenementOptions updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementOptions::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_options.index')
            ->with('success', 'EvenementOptions deleted successfully');
    }
                
}
