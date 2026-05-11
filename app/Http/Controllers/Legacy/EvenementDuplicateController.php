<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementDuplicate;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_duplicate.php
 * Legacy pattern: generic
 * Legacy permission id: 15
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementDuplicateController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementDuplicate::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('inputtypecheckboxvalue1namepidplabelforplepersonnel', 'like', '%' . $term . '%');
                $query->orWhere('back1divformwrite_msgboxquestion', 'like', '%' . $term . '%');
                $query->orWhere('question_pic', 'like', '%' . $term . '%');
                $query->orWhere('message', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_duplicate.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_duplicate.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementDuplicate::findOrFail($id);

        return view('legacy_migrated.evenement_duplicate.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'D' => 'nullable|string|max:255',
            'P' => 'nullable|string|max:255',
            'V' => 'nullable|string|max:255',
            'numweeks' => 'nullable|string|max:255',
        ]);

        $item = EvenementDuplicate::create([
            'evenement' => $validated['evenement'] ?? null,
            'D' => $validated['D'] ?? null,
            'P' => $validated['P'] ?? null,
            'V' => $validated['V'] ?? null,
            'numweeks' => $validated['numweeks'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_duplicate.edit', $item->id)
            ->with('success', 'EvenementDuplicate created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementDuplicate::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'D' => 'nullable|string|max:255',
            'P' => 'nullable|string|max:255',
            'V' => 'nullable|string|max:255',
            'numweeks' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'D' => $validated['D'] ?? null,
            'P' => $validated['P'] ?? null,
            'V' => $validated['V'] ?? null,
            'numweeks' => $validated['numweeks'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_duplicate.edit', $item->id)
            ->with('success', 'EvenementDuplicate updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementDuplicate::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_duplicate.index')
            ->with('success', 'EvenementDuplicate deleted successfully');
    }
                
}
