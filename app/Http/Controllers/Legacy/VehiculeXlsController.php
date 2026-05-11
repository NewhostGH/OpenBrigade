<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: vehicule_xls.php
 * Legacy pattern: export
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class VehiculeXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicule::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('v_id', 'like', '%' . $term . '%');
                $query->orWhere('vp_id', 'like', '%' . $term . '%');
                $query->orWhere('tv_code', 'like', '%' . $term . '%');
                $query->orWhere('v_modele', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.vehicule_xls.index', [
            'items' => $items,
        ]);
    }
}
