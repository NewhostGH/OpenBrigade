<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Materiel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: materiel_xls.php
 * Legacy pattern: export
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class MaterielXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Materiel::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tm_usage', 'like', '%' . $term . '%');
                $query->orWhere('tm_code', 'like', '%' . $term . '%');
                $query->orWhere('vp_libelle', 'like', '%' . $term . '%');
                $query->orWhere('ma_rev_date', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.materiel_xls.index', [
            'items' => $items,
        ]);
    }
}
