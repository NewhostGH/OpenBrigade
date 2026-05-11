<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use Illuminate\Http\Request;

/**
 * Legacy migration source: planning_xls.php
 * Legacy pattern: export
 * Legacy permission id: 56
 * This file stems from a legacy migration and requires functional verification.
 */
class PlanningXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Planning::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_sexe', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.planning_xls.index', [
            'items' => $items,
        ]);
    }
}
