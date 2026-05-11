<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Cotisations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: cotisations_xls.php
 * Legacy pattern: export
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class CotisationsXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotisations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('periode_code', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.cotisations_xls.index', [
            'items' => $items,
        ]);
    }
}
