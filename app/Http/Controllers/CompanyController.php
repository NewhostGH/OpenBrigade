<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $search = trim((string) $request->string('q'));
        $type   = (string) $request->string('type', 'ALL');

        $query = DB::table('company as c')
            ->leftJoin('type_company as tc', 'c.TC_CODE', '=', 'tc.TC_CODE')
            ->where('c.S_ID', $sectionId)
            ->select(
                'c.C_ID', 'c.C_NAME', 'c.C_EMAIL', 'c.C_PHONE',
                'c.C_CITY', 'c.C_ZIP_CODE', 'c.TC_CODE',
                'tc.TC_LIBELLE'
            )
            ->orderBy('c.C_NAME');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('c.C_NAME', 'like', "%{$search}%")
                  ->orWhere('c.C_CONTACT_NAME', 'like', "%{$search}%")
                  ->orWhere('c.C_EMAIL', 'like', "%{$search}%");
            });
        }

        if ($type !== 'ALL') {
            $query->where('c.TC_CODE', $type);
        }

        $items = $query->paginate(50)->withQueryString();

        $types = DB::table('type_company')->orderBy('TC_LIBELLE')->get(['TC_CODE', 'TC_LIBELLE']);

        return view('company.index', compact('items', 'search', 'type', 'types')
            + ['columns' => $this->companyColumns()]);
    }

    private function companyColumns(): array
    {
        return [
            ['key'=>'nom','label'=>'Nom','type'=>'text','value'=>fn($c)=>$c->C_NAME,'alwaysVisible'=>true,'sortField'=>'C_NAME','mobile'=>true],
            ['key'=>'type','label'=>'Type','type'=>'text','value'=>fn($c)=>$c->TC_LIBELLE ?? $c->TC_CODE ?? '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($c)=>$c->TC_LIBELLE ?? $c->TC_CODE ?? ''],
            ['key'=>'ville','label'=>'Ville','type'=>'text','value'=>fn($c)=>$c->C_CITY ? $c->C_CITY.($c->C_ZIP_CODE ? ' ('.$c->C_ZIP_CODE.')' : '') : '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($c)=>$c->C_CITY ?? ''],
            ['key'=>'telephone','label'=>'Téléphone','type'=>'html','value'=>fn($c)=>$c->C_PHONE ? '<a href="tel:'.e($c->C_PHONE).'" class="text-decoration-none">'.e($c->C_PHONE).'</a>' : '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($c)=>$c->C_PHONE ?? ''],
            ['key'=>'email','label'=>'E-mail','type'=>'html','value'=>fn($c)=>$c->C_EMAIL ? '<a href="mailto:'.e($c->C_EMAIL).'" class="text-decoration-none">'.e($c->C_EMAIL).'</a>' : '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($c)=>$c->C_EMAIL ?? ''],
        ];
    }
}
