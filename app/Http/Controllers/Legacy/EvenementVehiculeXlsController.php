<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementVehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_vehicule_xls.php
 * Legacy pattern: export
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementVehiculeXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementVehicule::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tv_code', 'like', '%' . $term . '%');
                $query->orWhere('v_id', 'like', '%' . $term . '%');
                $query->orWhere('v_immatriculation', 'like', '%' . $term . '%');
                $query->orWhere('v_modele', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_vehicule_xls.index', [
            'items' => $items,
        ]);
    }
}
