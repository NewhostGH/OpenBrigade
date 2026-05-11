<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use Illuminate\Http\Request;

/**
 * Legacy migration source: pdf_bulletin.php
 * Legacy pattern: pdf
 * Legacy permission id: 44
 * This file stems from a legacy migration and requires functional verification.
 */
class PdfBulletinController extends Controller
{
    public function index(Request $request)
    {
        $query = Bulletin::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_code', 'like', '%' . $term . '%');
                $query->orWhere('s_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.pdf_bulletin.index', [
            'items' => $items,
        ]);
    }
}
