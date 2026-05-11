<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\Request;

/**
 * Legacy migration source: reminder.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $query = Reminder::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('e_lieu', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.reminder.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.reminder.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Reminder::findOrFail($id);

        return view('legacy_migrated.reminder.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Reminder::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.reminder.edit', $item->id)
            ->with('success', 'Reminder created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Reminder::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.reminder.edit', $item->id)
            ->with('success', 'Reminder updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Reminder::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.reminder.index')
            ->with('success', 'Reminder deleted successfully');
    }
                
}
