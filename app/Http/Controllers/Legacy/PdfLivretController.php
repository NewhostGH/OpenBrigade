<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Livret;
use Illuminate\Http\Request;

/**
 * Legacy migration source: pdf_livret.php
 * Legacy pattern: pdf
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PdfLivretController extends Controller
{
    public function index(Request $request)
    {
        $query = Livret::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_code', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_nom_naissance', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.pdf_livret.index', [
            'items' => $items,
        ]);
    }
}
