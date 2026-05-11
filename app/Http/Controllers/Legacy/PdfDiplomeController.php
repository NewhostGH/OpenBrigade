<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Diplome;
use Illuminate\Http\Request;

/**
 * Legacy migration source: pdf_diplome.php
 * Legacy pattern: pdf
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PdfDiplomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Diplome::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ps_id', 'like', '%' . $term . '%');
                $query->orWhere('eh_date_debut', 'like', '%' . $term . '%');
                $query->orWhere('dmy', 'like', '%' . $term . '%');
                $query->orWhere('eh_date_fin', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.pdf_diplome.index', [
            'items' => $items,
        ]);
    }
}
