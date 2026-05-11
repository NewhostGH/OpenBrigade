<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\HistoSms;
use Illuminate\Http\Request;

/**
 * Legacy migration source: histo_sms.php
 * Legacy pattern: list
 * Legacy permission id: 23
 * This file stems from a legacy migration and requires functional verification.
 */
class HistoSmsController extends Controller
{
    public function index(Request $request)
    {
        $query = HistoSms::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('sms_account', 'like', '%' . $term . '%');
                $query->orWhere('dtdb', 'like', '%' . $term . '%');
                $query->orWhere('dtfn', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.histo_sms.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.histo_sms.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = HistoSms::findOrFail($id);

        return view('legacy_migrated.histo_sms.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'sms_account' => 'nullable|string|max:255',
        ]);

        $item = HistoSms::create([
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'type' => $validated['type'] ?? null,
            'sms_account' => $validated['sms_account'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.histo_sms.edit', $item->id)
            ->with('success', 'HistoSms created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = HistoSms::findOrFail($id);

        $validated = $request->validate([
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'sms_account' => 'nullable|string|max:255',
        ]);

        $item->update([
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'type' => $validated['type'] ?? null,
            'sms_account' => $validated['sms_account'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.histo_sms.edit', $item->id)
            ->with('success', 'HistoSms updated successfully');
    }
                

    public function destroy($id)
    {
        $item = HistoSms::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.histo_sms.index')
            ->with('success', 'HistoSms deleted successfully');
    }
                
}
