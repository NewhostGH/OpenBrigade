<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TableauGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: tableau_garde_xls.php
 * Legacy pattern: export
 * Legacy permission id: 61
 * This file stems from a legacy migration and requires functional verification.
 */
class TableauGardeXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = TableauGarde::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('eq_id', 'like', '%' . $term . '%');
                $query->orWhere('eq_nom', 'like', '%' . $term . '%');
                $query->orWhere('eq_jour', 'like', '%' . $term . '%');
                $query->orWhere('eq_nuit', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.tableau_garde_xls.index', [
            'items' => $items,
        ]);
    }
}
