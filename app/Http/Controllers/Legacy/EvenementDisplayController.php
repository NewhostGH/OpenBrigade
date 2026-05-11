<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementDisplay;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_display.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementDisplayController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementDisplay::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('documents_attachs', 'like', '%' . $term . '%');
                $query->orWhere('auteur', 'like', '%' . $term . '%');
                $query->orWhere('date', 'like', '%' . $term . '%');
                $query->orWhere('lh_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_display.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_display.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementDisplay::findOrFail($id);

        return view('legacy_migrated.evenement_display.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Messagesubject' => 'nullable|string|max:255',
            'Messagebody' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
            'exp_' => 'nullable|string|max:255',
            'update_hierarchy' => 'nullable|string|max:255',
            'evenement_show_absents' => 'nullable|string|max:255',
            'autorefresh' => 'nullable|string|max:255',
            'nombre' => 'nullable|string|max:255',
            'evenement_periode' => 'nullable|string|max:255',
            'evenement_date' => 'nullable|string|max:255',
        ]);

        $item = EvenementDisplay::create([
            'Messagesubject' => $validated['Messagesubject'] ?? null,
            'Messagebody' => $validated['Messagebody'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
            'exp_' => $validated['exp_'] ?? null,
            'update_hierarchy' => $validated['update_hierarchy'] ?? null,
            'evenement_show_absents' => $validated['evenement_show_absents'] ?? null,
            'autorefresh' => $validated['autorefresh'] ?? null,
            'nombre' => $validated['nombre'] ?? null,
            'evenement_periode' => $validated['evenement_periode'] ?? null,
            'evenement_date' => $validated['evenement_date'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_display.edit', $item->id)
            ->with('success', 'EvenementDisplay created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementDisplay::findOrFail($id);

        $validated = $request->validate([
            'Messagesubject' => 'nullable|string|max:255',
            'Messagebody' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
            'exp_' => 'nullable|string|max:255',
            'update_hierarchy' => 'nullable|string|max:255',
            'evenement_show_absents' => 'nullable|string|max:255',
            'autorefresh' => 'nullable|string|max:255',
            'nombre' => 'nullable|string|max:255',
            'evenement_periode' => 'nullable|string|max:255',
            'evenement_date' => 'nullable|string|max:255',
        ]);

        $item->update([
            'Messagesubject' => $validated['Messagesubject'] ?? null,
            'Messagebody' => $validated['Messagebody'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
            'exp_' => $validated['exp_'] ?? null,
            'update_hierarchy' => $validated['update_hierarchy'] ?? null,
            'evenement_show_absents' => $validated['evenement_show_absents'] ?? null,
            'autorefresh' => $validated['autorefresh'] ?? null,
            'nombre' => $validated['nombre'] ?? null,
            'evenement_periode' => $validated['evenement_periode'] ?? null,
            'evenement_date' => $validated['evenement_date'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_display.edit', $item->id)
            ->with('success', 'EvenementDisplay updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementDisplay::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_display.index')
            ->with('success', 'EvenementDisplay deleted successfully');
    }
                
}
