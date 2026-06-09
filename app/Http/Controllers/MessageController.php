<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Message board — consignes opérationnelles and news (actualités).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        // Which category: consigne | amicale (actualité) | all
        $category = (string) $request->string('category', 'consigne');
        $allowed = ['consigne', 'amicale', 'all'];
        if (! in_array($category, $allowed, true)) {
            $category = 'consigne';
        }

        $query = DB::table('message as m')
            ->leftJoin('pompier as p', 'm.P_ID', '=', 'p.P_ID')
            ->where('m.S_ID', $sectionId)
            ->where(function ($q) {
                $today = now()->toDateString();
                $q->whereNull('m.M_DUREE')
                    ->orWhere(DB::raw('DATE_ADD(m.M_DATE, INTERVAL m.M_DUREE DAY)'), '>=', $today);
            })
            ->select(
                'm.M_ID', 'm.M_OBJET', 'm.M_TEXTE', 'm.M_DATE',
                'm.M_TYPE', 'm.M_FILE',
                DB::raw("CONCAT(p.P_PRENOM, ' ', p.P_NOM) as author")
            )
            ->orderByDesc('m.M_DATE');

        if ($category !== 'all') {
            $query->where('m.M_TYPE', $category);
        }

        $items = $query->paginate(20)->withQueryString();

        return view('message.index', compact('items', 'category'));
    }
}
