<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Habilitations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: habilitations_xls.php
 * Legacy pattern: export
 * Legacy permission id: 40
 * This file stems from a legacy migration and requires functional verification.
 */
class HabilitationsXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Habilitations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('gp_description', 'like', '%' . $term . '%');
                $query->orWhere('p_idid', 'like', '%' . $term . '%');
                $query->orWhere('p_emailemail', 'like', '%' . $term . '%');
                $query->orWhere('p_nomnom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.habilitations_xls.index', [
            'items' => $items,
        ]);
    }
}
