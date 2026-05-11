<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel_xls.php
 * Legacy pattern: export
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Personnel::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_gradegrade', 'like', '%' . $term . '%');
                $query->orWhere('p_statutstatut', 'like', '%' . $term . '%');
                $query->orWhere('p_prenomprenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel_xls.index', [
            'items' => $items,
        ]);
    }
}
