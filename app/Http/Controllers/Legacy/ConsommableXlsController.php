<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Consommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: consommable_xls.php
 * Legacy pattern: export
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class ConsommableXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Consommable::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('c_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('tc_id', 'like', '%' . $term . '%');
                $query->orWhere('c_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.consommable_xls.index', [
            'items' => $items,
        ]);
    }
}
