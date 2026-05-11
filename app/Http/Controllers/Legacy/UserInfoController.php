<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\UserInfo;
use Illuminate\Http\Request;

/**
 * Legacy migration source: user_info.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class UserInfoController extends Controller
{
    public function index(Request $request)
    {
        $query = UserInfo::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_code', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.user_info.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.user_info.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = UserInfo::findOrFail($id);

        return view('legacy_migrated.user_info.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = UserInfo::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.user_info.edit', $item->id)
            ->with('success', 'UserInfo created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = UserInfo::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.user_info.edit', $item->id)
            ->with('success', 'UserInfo updated successfully');
    }
                

    public function destroy($id)
    {
        $item = UserInfo::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.user_info.index')
            ->with('success', 'UserInfo deleted successfully');
    }
                
}
