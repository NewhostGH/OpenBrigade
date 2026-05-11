<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Mailer;
use Illuminate\Http\Request;

/**
 * Legacy migration source: mailer.php
 * Legacy pattern: list
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class MailerController extends Controller
{
    public function index(Request $request)
    {
        $query = Mailer::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('maildate', 'like', '%' . $term . '%');
                $query->orWhere('mailto', 'like', '%' . $term . '%');
                $query->orWhere('sendername', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.mailer.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.mailer.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Mailer::findOrFail($id);

        return view('legacy_migrated.mailer.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Mailer::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mailer.edit', $item->id)
            ->with('success', 'Mailer created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Mailer::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mailer.edit', $item->id)
            ->with('success', 'Mailer updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Mailer::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.mailer.index')
            ->with('success', 'Mailer deleted successfully');
    }
                
}
