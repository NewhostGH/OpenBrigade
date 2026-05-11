<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Pdf;
use Illuminate\Http\Request;

/**
 * Legacy migration source: pdf.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PdfController extends Controller
{
    public function index(Request $request)
    {
        $query = Pdf::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_description', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nomp_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenomp_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.pdf.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.pdf.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Pdf::findOrFail($id);

        return view('legacy_migrated.pdf.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pdf' => 'nullable|string|max:255',
            'id' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item = Pdf::create([
            'pdf' => $validated['pdf'] ?? null,
            'id' => $validated['id'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.pdf.edit', $item->id)
            ->with('success', 'Pdf created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Pdf::findOrFail($id);

        $validated = $request->validate([
            'pdf' => 'nullable|string|max:255',
            'id' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pdf' => $validated['pdf'] ?? null,
            'id' => $validated['id'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.pdf.edit', $item->id)
            ->with('success', 'Pdf updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Pdf::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.pdf.index')
            ->with('success', 'Pdf deleted successfully');
    }
                
}
