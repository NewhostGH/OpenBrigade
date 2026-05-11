<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AttestationFiscale;
use Illuminate\Http\Request;

/**
 * Legacy migration source: pdf_attestation_fiscale.php
 * Legacy pattern: pdf
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PdfAttestationFiscaleController extends Controller
{
    public function index(Request $request)
    {
        $query = AttestationFiscale::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_code', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.pdf_attestation_fiscale.index', [
            'items' => $items,
        ]);
    }
}
