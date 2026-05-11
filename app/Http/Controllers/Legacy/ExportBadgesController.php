<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ExportBadges;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export_badges.php
 * Legacy pattern: export
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportBadgesController extends Controller
{
    public function index(Request $request)
    {
        $query = ExportBadges::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('tc_libelle', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export_badges.index', [
            'items' => $items,
        ]);
    }
}
