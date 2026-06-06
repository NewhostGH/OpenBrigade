<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Personnel;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\ICalExportService;
use App\Services\TableExportService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EvenementController extends Controller
{
    // ── Event list ────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $period   = (string) $request->string('period', 'upcoming');
        $search   = trim((string) $request->string('q'));
        $type     = (string) $request->string('type', 'ALL');
        $filtSect = (int) $request->integer('section', 0);

        $query = Evenement::query()
            ->with(['horaires', 'section'])
            ->join('evenement_horaire as eh', 'evenement.E_CODE', '=', 'eh.E_CODE')
            ->join('type_evenement as te', 'evenement.TE_CODE', '=', 'te.TE_CODE')
            ->select([
                'evenement.E_CODE',
                'evenement.E_LIBELLE',
                'evenement.E_LIEU',
                'evenement.E_CLOSED',
                'evenement.E_CANCELED',
                'evenement.E_NB',
                'evenement.S_ID',
                'evenement.TE_CODE',
                'evenement.E_EQUIPE',
                'te.TE_LIBELLE',
                'te.TE_ICON',
                DB::raw("MIN(eh.EH_DATE_DEBUT) as first_date"),
                DB::raw("MIN(eh.EH_DEBUT) as first_time"),
            ])
            ->groupBy(
                'evenement.E_CODE', 'evenement.E_LIBELLE', 'evenement.E_LIEU',
                'evenement.E_CLOSED', 'evenement.E_CANCELED', 'evenement.E_NB',
                'evenement.S_ID', 'evenement.TE_CODE', 'evenement.E_EQUIPE',
                'te.TE_LIBELLE', 'te.TE_ICON'
            )
            ->where('evenement.E_CANCELED', 0)
            ->where('evenement.E_EQUIPE', 0);

        match ($period) {
            'past'     => $query->having(DB::raw("MIN(eh.EH_DATE_DEBUT)"), '<', now()->toDateString()),
            'upcoming' => $query->having(DB::raw("MAX(eh.EH_DATE_FIN)"), '>=', now()->toDateString()),
            default    => null, // 'all'
        };

        if ($type !== '' && $type !== 'ALL') {
            $query->where('evenement.TE_CODE', $type);
        }

        if ($filtSect > 0) {
            $query->where('evenement.S_ID', $filtSect);
        } else {
            $query->where('evenement.S_ID', $sectionId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('evenement.E_LIBELLE', 'like', "%{$search}%")
                  ->orWhere('evenement.E_LIEU', 'like', "%{$search}%")
                  ->orWhere('evenement.E_CODE', 'like', "%{$search}%");
            });
        }

        if ($period === 'past') {
            $query->orderByDesc('first_date');
        } else {
            $query->orderBy('first_date');
        }

        $items = $query->paginate(50)->withQueryString();

        // Event types for the filter dropdown
        $types = DB::table('type_evenement')
            ->where('TE_CODE', '<>', 'MC')
            ->orderBy('TE_LIBELLE')
            ->get(['TE_CODE', 'TE_LIBELLE']);

        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('evenement.index', compact(
            'items', 'period', 'search', 'type', 'filtSect', 'types', 'sections'
        ) + ['columns' => $this->evenementColumns()]);
    }

    private static function typeIcon(string $code): string
    {
        return match ($code) {
            'GAR'                    => 'shield-alt',
            'FOR'                    => 'graduation-cap',
            'REU'                    => 'users',
            'CER'                    => 'award',
            'SPO'                    => 'running',
            'TEC'                    => 'tools',
            'COM'                    => 'bullhorn',
            'MLA'                    => 'tasks',
            'WEB'                    => 'video',
            'MC'                     => 'clipboard-list',
            'DPS'                    => 'medkit',
            'MAN', 'EXE'             => 'dumbbell',
            'NAU'                    => 'water',
            'MAR'                    => 'walking',
            'MED'                    => 'heartbeat',
            'COH', 'DIV'             => 'calendar-alt',
            'ALR'                    => 'bell',
            'HUM', 'HEB', 'AID'      => 'hands-helping',
            'DFCI', 'FOR_INC'        => 'fire',
            'LOG', 'TRANS'           => 'truck',
            'RAD', 'RADIO'           => 'broadcast-tower',
            default                  => 'calendar-alt',
        };
    }

    private function evenementColumns(): array
    {
        return [
            ['key'=>'icon','label'=>'','type'=>'html','value'=>fn($e) => '<i class="fas fa-'.self::typeIcon($e->TE_CODE ?? '').'" style="color:var(--text-muted-soft)" title="'.e($e->TE_LIBELLE ?? '').'"></i>','alwaysVisible'=>true,'exportable'=>false,'mobile'=>true],
            ['key'=>'activite','label'=>'Activité','type'=>'html','value'=>fn($e)=>'<a href="'.route('evenement.show',$e->E_CODE).'" class="text-decoration-none fw-semibold">'.e($e->E_LIBELLE ?? $e->E_CODE).'</a>','alwaysVisible'=>true,'exportable'=>true,'exportValue'=>fn($e)=>$e->E_LIBELLE ?? $e->E_CODE,'sortField'=>'E_INTITULE','mobile'=>true],
            ['key'=>'lieu','label'=>'Lieu','type'=>'text','value'=>fn($e)=>$e->E_LIEU ?? '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($e)=>$e->E_LIEU ?? ''],
            ['key'=>'date','label'=>'Date','type'=>'html','value'=>fn($e)=>$e->first_date ? \Carbon\Carbon::parse($e->first_date)->locale('fr')->isoFormat('ddd D MMM YYYY').($e->first_time ? ' <span class="text-muted">'.substr($e->first_time,0,5).'</span>' : '') : '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($e)=>$e->first_date?\Carbon\Carbon::parse($e->first_date)->format('d/m/Y'):''],
            ['key'=>'statut','label'=>'Statut','type'=>'badge','value'=>fn($e)=>$e->E_CANCELED ? 'ANNULEE' : ($e->E_CLOSED ? 'CLOSE' : 'OPEN'),'badgeMap'=>['ANNULEE'=>['Annulée','ob-badge-bloqued'],'CLOSE'=>['Clôturée','ob-badge-archive'],'OPEN'=>['Ouverte','ob-badge-actif']],'exportable'=>true,'exportValue'=>fn($e)=>$e->E_CANCELED ? 'Annulée' : ($e->E_CLOSED ? 'Clôturée' : 'Ouverte'),'mobile'=>false],
        ];
    }

    // ── Event detail ──────────────────────────────────────────────────────────

    public function show(string $code): View
    {
        $event = Evenement::with(['section', 'horaires', 'chef'])->findOrFail($code);

        $typeLabel = DB::table('type_evenement')
            ->where('TE_CODE', $event->TE_CODE)
            ->value('TE_LIBELLE');

        // Participant functions for this event type (shown in add/edit modals).
        $functions = DB::table('type_participation')
            ->where('TE_CODE', $event->TE_CODE)
            ->orderBy('TP_NUM')
            ->get(['TP_ID', 'TP_LIBELLE']);

        // Teams with member/vehicle counts (also needed by participant modals).
        $equipes = $this->loadEquipes($code);

        // Enrolled participants — one row per person.
        $participants = DB::table('evenement_participation as ep')
            ->join('pompier as p', 'ep.P_ID', '=', 'p.P_ID')
            ->leftJoin('type_participation as tp', 'tp.TP_ID', '=', 'ep.TP_ID')
            ->leftJoin('evenement_equipe as ee', function ($j) use ($code) {
                $j->on('ee.EE_ID', '=', 'ep.EE_ID')->where('ee.E_CODE', '=', $code);
            })
            ->where('ep.E_CODE', $code)
            ->groupBy(
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_PHOTO',
                'p.P_GRADE', 'p.P_STATUT', 'ep.TP_ID', 'tp.TP_LIBELLE',
                'ep.EE_ID', 'ee.EE_NAME', 'ep.EP_COMMENT', 'ep.EP_ABSENT'
            )
            ->orderBy('p.P_NOM')
            ->orderBy('p.P_PRENOM')
            ->select(
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_PHOTO',
                'p.P_GRADE', 'p.P_STATUT', 'ep.TP_ID', 'tp.TP_LIBELLE',
                'ep.EE_ID', 'ee.EE_NAME', 'ep.EP_COMMENT', 'ep.EP_ABSENT'
            )
            ->get();

        // Members not yet enrolled — candidates for the add-participant modal.
        $candidates = DB::table('pompier as p')
            ->where('p.P_OLD_MEMBER', 0)
            ->whereNotExists(function ($q) use ($code) {
                $q->select(DB::raw(1))
                  ->from('evenement_participation as ep')
                  ->whereColumn('ep.P_ID', 'p.P_ID')
                  ->where('ep.E_CODE', $code);
            })
            ->orderBy('p.P_NOM')
            ->orderBy('p.P_PRENOM')
            ->get(['p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_GRADE']);

        // Vehicles assigned to this event.
        $vehicules = DB::table('evenement_vehicule as ev')
            ->join('vehicule as v', 'ev.V_ID', '=', 'v.V_ID')
            ->where('ev.E_CODE', $code)
            ->select('v.V_ID', 'v.V_IMMATRICULATION', 'v.V_INDICATIF', 'ev.EV_KM')
            ->get();

        // All vehicles (for assignment modal).
        $allVehicles = DB::table('vehicule')
            ->orderBy('V_IMMATRICULATION')
            ->orderBy('V_INDICATIF')
            ->get(['V_ID', 'V_IMMATRICULATION', 'V_INDICATIF']);

        // Materials assigned to this event.
        $materiels = DB::table('evenement_materiel as em')
            ->join('materiel as m', 'em.MA_ID', '=', 'm.MA_ID')
            ->where('em.E_CODE', $code)
            ->orderBy('m.MA_MODELE')
            ->select('m.MA_ID', 'm.MA_MODELE', 'm.MA_NUMERO_SERIE', 'em.EM_NB', 'em.EE_ID')
            ->get();

        // All materials (for the assign modal).
        $allMateriels = DB::table('materiel')
            ->orderBy('MA_MODELE')
            ->get(['MA_ID', 'MA_MODELE', 'MA_NUMERO_SERIE']);

        // Reinforcement sub-events.
        $renforts = DB::table('evenement as e')
            ->leftJoin('evenement_participation as ep', function ($j) {
                $j->on('ep.E_CODE', '=', 'e.E_CODE')->where('ep.EP_ABSENT', '=', 0);
            })
            ->where('e.E_PARENT', $code)
            ->groupBy('e.E_CODE', 'e.E_LIBELLE', 'e.E_LIEU', 'e.E_CANCELED')
            ->orderBy('e.E_CODE')
            ->select(
                'e.E_CODE', 'e.E_LIBELLE', 'e.E_LIEU', 'e.E_CANCELED',
                DB::raw('COUNT(DISTINCT ep.P_ID) as participant_count')
            )
            ->get();

        return view('evenement.show', compact(
            'event', 'typeLabel', 'participants', 'candidates', 'vehicules', 'allVehicles',
            'functions', 'equipes', 'renforts', 'materiels', 'allMateriels'
        ));
    }

    /** Teams (equipes) for an event with member + vehicle counts. */
    private function loadEquipes(string|int $code): \Illuminate\Support\Collection
    {
        return DB::table('evenement_equipe as ee')
            ->where('ee.E_CODE', $code)
            ->orderBy('ee.EE_ORDER')
            ->orderBy('ee.EE_NAME')
            ->select('ee.EE_ID', 'ee.EE_NAME', 'ee.EE_DESCRIPTION', 'ee.EE_ORDER', 'ee.EE_ID_RADIO')
            ->get()
            ->map(function ($e) use ($code) {
                $e->member_count = DB::table('evenement_participation')
                    ->where('E_CODE', $code)->where('EE_ID', $e->EE_ID)
                    ->distinct()->count('P_ID');
                $e->vehicle_count = DB::table('evenement_vehicule')
                    ->where('E_CODE', $code)->where('EE_ID', $e->EE_ID)
                    ->distinct()->count('V_ID');
                return $e;
            });
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        [$groupedTypes, $sections, $chefs] = $this->formLookups();

        return view('evenement.form', [
            'event'        => null,
            'horaire'      => null,
            'groupedTypes' => $groupedTypes,
            'sections'     => $sections,
            'chefs'        => $chefs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEventRequest($request, isCreate: true);

        $code = DB::transaction(function () use ($validated, $request) {
            $code = (int) DB::table('evenement')->max('E_CODE') + 1;

            DB::table('evenement')->insert([
                'E_CODE'                => $code,
                'TE_CODE'               => $validated['TE_CODE'],
                'S_ID'                  => $validated['S_ID'],
                'E_LIBELLE'             => $validated['E_LIBELLE'],
                'E_LIEU'                => $validated['E_LIEU'] ?? '',
                'E_ADDRESS'             => $validated['E_ADDRESS'] ?? null,
                'E_LIEU_RDV'            => $validated['E_LIEU_RDV'] ?? null,
                'E_HEURE_RDV'           => $validated['E_HEURE_RDV'] ?? null,
                'E_NB'                  => $validated['E_NB'] ?? 0,
                'E_CHEF'                => $validated['E_CHEF'] ?? null,
                'E_TEL'                 => $validated['E_TEL'] ?? null,
                'E_COMMENT'             => $validated['E_COMMENT'] ?? '',
                'E_CONSIGNES'           => $validated['E_CONSIGNES'] ?? null,
                'E_CONTACT_LOCAL'       => $validated['E_CONTACT_LOCAL'] ?? null,
                'E_CONTACT_TEL'         => $validated['E_CONTACT_TEL'] ?? null,
                'E_WHATSAPP'            => $validated['E_WHATSAPP'] ?? null,
                'E_WEBEX_URL'           => $validated['E_WEBEX_URL'] ?? null,
                'E_WEBEX_PIN'           => $validated['E_WEBEX_PIN'] ?? null,
                'E_WEBEX_START'         => $validated['E_WEBEX_START'] ?? null,
                'E_CLOSED'              => 0,
                'E_CANCELED'            => 0,
                'E_EQUIPE'              => 0,
                'E_ANOMALIE'            => 0,
                'E_FLAG1'               => 0,
                'E_OPEN_TO_EXT'         => (int) $request->boolean('E_OPEN_TO_EXT'),
                'E_VISIBLE_OUTSIDE'     => (int) $request->boolean('E_VISIBLE_OUTSIDE'),
                'E_EXTERIEUR'           => (int) $request->boolean('E_EXTERIEUR'),
                'E_VISIBLE_INSIDE'      => (int) !$request->boolean('E_HIDDEN'),
                'E_ALLOW_REINFORCEMENT' => (int) $request->boolean('E_ALLOW_REINFORCEMENT'),
                'E_AUTOCLOSE_BEFORE'    => $validated['E_AUTOCLOSE_BEFORE'] ?? null,
                'E_CREATED_BY'          => auth()->id(),
                'E_CREATE_DATE'         => now(),
            ]);

            foreach ($validated['horaires'] as $i => $h) {
                $dateDebut = Carbon::parse($h['EH_DATE_DEBUT'])->toDateString();
                $dateFin   = !empty($h['EH_DATE_FIN'])
                    ? Carbon::parse($h['EH_DATE_FIN'])->toDateString()
                    : $dateDebut;

                DB::table('evenement_horaire')->insert([
                    'E_CODE'         => $code,
                    'EH_ID'          => $i + 1,
                    'EH_DATE_DEBUT'  => $dateDebut,
                    'EH_DATE_FIN'    => $dateFin,
                    'EH_DEBUT'       => !empty($h['EH_DEBUT']) ? $h['EH_DEBUT'] : '00:00:00',
                    'EH_FIN'         => !empty($h['EH_FIN'])   ? $h['EH_FIN']   : '00:00:00',
                    'EH_DUREE'       => 0,
                    'EH_DESCRIPTION' => '',
                ]);
            }

            return $code;
        });

        return redirect()->route('evenement.show', $code)
            ->with('success', 'Activité créée avec succès.');
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(string $code): View
    {
        $event    = Evenement::with(['horaires'])->findOrFail($code);
        $horaires = $event->horaires->sortBy('EH_ID')->values();

        [$groupedTypes, $sections, $chefs] = $this->formLookups();

        return view('evenement.form', compact('event', 'horaires', 'groupedTypes', 'sections', 'chefs'));
    }

    public function update(Request $request, string $code): RedirectResponse
    {
        $event     = Evenement::findOrFail($code);
        $validated = $this->validateEventRequest($request, isCreate: false);

        DB::transaction(function () use ($event, $validated, $request) {
            $event->update([
                'TE_CODE'               => $validated['TE_CODE'],
                'S_ID'                  => $validated['S_ID'],
                'E_LIBELLE'             => $validated['E_LIBELLE'],
                'E_LIEU'                => $validated['E_LIEU'] ?? '',
                'E_ADDRESS'             => $validated['E_ADDRESS'] ?? null,
                'E_LIEU_RDV'            => $validated['E_LIEU_RDV'] ?? null,
                'E_HEURE_RDV'           => $validated['E_HEURE_RDV'] ?? null,
                'E_NB'                  => $validated['E_NB'] ?? 0,
                'E_CHEF'                => $validated['E_CHEF'] ?? null,
                'E_TEL'                 => $validated['E_TEL'] ?? null,
                'E_COMMENT'             => $validated['E_COMMENT'] ?? '',
                'E_CONSIGNES'           => $validated['E_CONSIGNES'] ?? null,
                'E_CONTACT_LOCAL'       => $validated['E_CONTACT_LOCAL'] ?? null,
                'E_CONTACT_TEL'         => $validated['E_CONTACT_TEL'] ?? null,
                'E_WHATSAPP'            => $validated['E_WHATSAPP'] ?? null,
                'E_WEBEX_URL'           => $validated['E_WEBEX_URL'] ?? null,
                'E_WEBEX_PIN'           => $validated['E_WEBEX_PIN'] ?? null,
                'E_WEBEX_START'         => $validated['E_WEBEX_START'] ?? null,
                'E_CLOSED'              => (int) $request->boolean('E_CLOSED'),
                'E_CANCELED'            => (int) $request->boolean('E_CANCELED'),
                'E_OPEN_TO_EXT'         => (int) $request->boolean('E_OPEN_TO_EXT'),
                'E_VISIBLE_OUTSIDE'     => (int) $request->boolean('E_VISIBLE_OUTSIDE'),
                'E_EXTERIEUR'           => (int) $request->boolean('E_EXTERIEUR'),
                'E_VISIBLE_INSIDE'      => (int) !$request->boolean('E_HIDDEN'),
                'E_ALLOW_REINFORCEMENT' => (int) $request->boolean('E_ALLOW_REINFORCEMENT'),
                'E_AUTOCLOSE_BEFORE'    => $validated['E_AUTOCLOSE_BEFORE'] ?? null,
            ]);

            $submittedIds = [];
            foreach ($validated['horaires'] as $i => $h) {
                $ehId = $i + 1;
                $submittedIds[] = $ehId;

                $dateDebut = Carbon::parse($h['EH_DATE_DEBUT'])->toDateString();
                $dateFin   = !empty($h['EH_DATE_FIN'])
                    ? Carbon::parse($h['EH_DATE_FIN'])->toDateString()
                    : $dateDebut;

                DB::table('evenement_horaire')->updateOrInsert(
                    ['E_CODE' => $event->E_CODE, 'EH_ID' => $ehId],
                    [
                        'EH_DATE_DEBUT'  => $dateDebut,
                        'EH_DATE_FIN'    => $dateFin,
                        'EH_DEBUT'       => !empty($h['EH_DEBUT']) ? $h['EH_DEBUT'] : '00:00:00',
                        'EH_FIN'         => !empty($h['EH_FIN'])   ? $h['EH_FIN']   : '00:00:00',
                        'EH_DUREE'       => 0,
                        'EH_DESCRIPTION' => '',
                    ]
                );
            }

            // Remove horaires that were deleted in the form
            DB::table('evenement_horaire')
                ->where('E_CODE', $event->E_CODE)
                ->whereNotIn('EH_ID', $submittedIds)
                ->delete();
        });

        return redirect()->route('evenement.show', $code)
            ->with('success', 'Activité mise à jour.');
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(string $code): RedirectResponse
    {
        $event = Evenement::findOrFail($code);

        DB::transaction(function () use ($event) {
            $c = $event->E_CODE;
            DB::table('evenement_participation')->where('E_CODE', $c)->delete();
            DB::table('evenement_vehicule')->where('E_CODE', $c)->delete();
            DB::table('evenement_materiel')->where('E_CODE', $c)->delete();
            DB::table('evenement_chef')->where('E_CODE', $c)->delete();
            DB::table('evenement_horaire')->where('E_CODE', $c)->delete();
            $event->delete();
        });

        return redirect()->route('evenement.index')
            ->with('success', 'Activité supprimée.');
    }

    // ── Participant management ────────────────────────────────────────────────

    public function participantStore(Request $request, string $code): RedirectResponse
    {
        $event = Evenement::findOrFail($code);

        $validated = $request->validate([
            'P_ID'     => ['required', 'integer', 'exists:pompier,P_ID'],
            'EH_ID'    => ['required', 'integer', 'exists:evenement_horaire,EH_ID'],
            'TP_ID'    => ['nullable', 'integer'],
            'EE_ID'    => ['nullable', 'integer'],
            'EP_COMMENT' => ['nullable', 'string', 'max:150'],
        ]);

        $already = DB::table('evenement_participation')
            ->where('E_CODE', $code)
            ->where('P_ID', $validated['P_ID'])
            ->where('EH_ID', $validated['EH_ID'])
            ->exists();

        if ($already) {
            return redirect()->route('evenement.show', [$code, 'tab' => 'personnel'])
                ->with('error', 'Ce membre est déjà inscrit sur ce créneau.');
        }

        $duree = DB::table('evenement_horaire')
            ->where('E_CODE', $code)
            ->where('EH_ID', $validated['EH_ID'])
            ->value('EH_DUREE');

        DB::table('evenement_participation')->insert([
            'E_CODE'     => $code,
            'EH_ID'      => $validated['EH_ID'],
            'P_ID'       => $validated['P_ID'],
            'EP_DATE'    => now(),
            'EP_BY'      => auth()->id(),
            'TP_ID'      => $validated['TP_ID'] ?? 0,
            'EE_ID'      => $validated['EE_ID'] ?? null,
            'EP_COMMENT' => $validated['EP_COMMENT'] ?? null,
            'EP_DUREE'   => $duree ?? 0,
            'EP_ABSENT'  => 0,
            'EP_FLAG1'   => 0,
        ]);

        return redirect()->route('evenement.show', [$code, 'tab' => 'personnel'])
            ->with('success', 'Participant ajouté.');
    }

    public function participantUpdate(Request $request, string $code, int $pid): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate([
            'TP_ID'      => ['nullable', 'integer'],
            'EE_ID'      => ['nullable', 'integer'],
            'EP_COMMENT' => ['nullable', 'string', 'max:150'],
            'EP_ABSENT'  => ['boolean'],
        ]);

        DB::table('evenement_participation')
            ->where('E_CODE', $code)
            ->where('P_ID', $pid)
            ->update([
                'TP_ID'      => $validated['TP_ID'] ?? 0,
                'EE_ID'      => $validated['EE_ID'] ?? null,
                'EP_COMMENT' => $validated['EP_COMMENT'] ?? null,
                'EP_ABSENT'  => $request->boolean('EP_ABSENT') ? 1 : 0,
            ]);

        return redirect()->route('evenement.show', [$code, 'tab' => 'personnel'])
            ->with('success', 'Participation mise à jour.');
    }

    public function participantDestroy(string $code, int $pid): RedirectResponse
    {
        Evenement::findOrFail($code);

        DB::table('evenement_participation')
            ->where('E_CODE', $code)
            ->where('P_ID', $pid)
            ->delete();

        return redirect()->route('evenement.show', [$code, 'tab' => 'personnel'])
            ->with('success', 'Participant retiré.');
    }

    // ── Équipes CRUD ──────────────────────────────────────────────────────────

    public function equipeStore(Request $request, string $code): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate([
            'EE_NAME'        => ['required', 'string', 'max:30'],
            'EE_ORDER'       => ['nullable', 'integer', 'min:1', 'max:50'],
            'EE_DESCRIPTION' => ['nullable', 'string', 'max:300'],
            'EE_ID_RADIO'    => ['nullable', 'string', 'max:12'],
        ]);

        $newId = (int) DB::table('evenement_equipe')
            ->where('E_CODE', $code)
            ->max('EE_ID') + 1;

        DB::table('evenement_equipe')->insert([
            'E_CODE'         => $code,
            'EE_ID'          => $newId ?: 1,
            'EE_NAME'        => $validated['EE_NAME'],
            'EE_ORDER'       => $validated['EE_ORDER'] ?? 1,
            'EE_DESCRIPTION' => $validated['EE_DESCRIPTION'] ?? '',
            'EE_ID_RADIO'    => $validated['EE_ID_RADIO'] ?? null,
            'EE_SIGNATURE'   => 0,
        ]);

        return redirect()->route('evenement.show', [$code, 'tab' => 'equipes'])
            ->with('success', 'Équipe créée.');
    }

    public function equipeUpdate(Request $request, string $code, int $ee): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate([
            'EE_NAME'        => ['required', 'string', 'max:30'],
            'EE_ORDER'       => ['nullable', 'integer', 'min:1', 'max:50'],
            'EE_DESCRIPTION' => ['nullable', 'string', 'max:300'],
            'EE_ID_RADIO'    => ['nullable', 'string', 'max:12'],
        ]);

        DB::table('evenement_equipe')
            ->where('E_CODE', $code)
            ->where('EE_ID', $ee)
            ->update([
                'EE_NAME'        => $validated['EE_NAME'],
                'EE_ORDER'       => $validated['EE_ORDER'] ?? 1,
                'EE_DESCRIPTION' => $validated['EE_DESCRIPTION'] ?? '',
                'EE_ID_RADIO'    => $validated['EE_ID_RADIO'] ?? null,
            ]);

        return redirect()->route('evenement.show', [$code, 'tab' => 'equipes'])
            ->with('success', 'Équipe mise à jour.');
    }

    public function equipeDestroy(string $code, int $ee): RedirectResponse
    {
        Evenement::findOrFail($code);

        DB::transaction(function () use ($code, $ee) {
            DB::table('evenement_equipe')
                ->where('E_CODE', $code)->where('EE_ID', $ee)->delete();
            DB::table('evenement_participation')
                ->where('E_CODE', $code)->where('EE_ID', $ee)
                ->update(['EE_ID' => null]);
            DB::table('evenement_vehicule')
                ->where('E_CODE', $code)->where('EE_ID', $ee)
                ->update(['EE_ID' => null]);
        });

        return redirect()->route('evenement.show', [$code, 'tab' => 'equipes'])
            ->with('success', 'Équipe supprimée.');
    }

    // ── Renforts (reinforcement sub-events) ──────────────────────────────────

    public function renfortAttach(Request $request, string $code): RedirectResponse
    {
        $event = Evenement::findOrFail($code);

        $validated = $request->validate([
            'renfort' => ['required', 'integer', 'different:' . $code],
        ]);

        $renfort = Evenement::findOrFail($validated['renfort']);

        if ($renfort->E_PARENT) {
            return redirect()->route('evenement.show', [$code, 'tab' => 'renforts'])
                ->with('error', 'Cet événement est déjà rattaché à un autre événement principal.');
        }
        if ($renfort->E_CANCELED) {
            return redirect()->route('evenement.show', [$code, 'tab' => 'renforts'])
                ->with('error', 'Cet événement est annulé et ne peut pas être rattaché.');
        }

        $renfort->update(['E_PARENT' => $event->E_CODE]);

        return redirect()->route('evenement.show', [$code, 'tab' => 'renforts'])
            ->with('success', 'Renfort rattaché.');
    }

    public function renfortDetach(string $code, string $renfort): RedirectResponse
    {
        Evenement::findOrFail($code);

        Evenement::where('E_CODE', $renfort)
            ->where('E_PARENT', $code)
            ->update(['E_PARENT' => null]);

        return redirect()->route('evenement.show', [$code, 'tab' => 'renforts'])
            ->with('success', 'Renfort détaché.');
    }

    // ── Vehicle assignment ─────────────────────────────────────────────────────

    public function vehiculeAttach(Request $request, string $code): RedirectResponse
    {
        $event = Evenement::findOrFail($code);

        $validated = $request->validate([
            'V_ID' => ['required', 'integer', 'exists:vehicule,V_ID'],
        ]);

        $already = $event->vehicules()->where('vehicule.V_ID', $validated['V_ID'])->exists();
        if ($already) {
            return redirect()->route('evenement.show', $code)
                ->with('error', 'Ce véhicule est déjà assigné à cette activité.');
        }

        $event->vehicules()->attach($validated['V_ID']);

        return redirect()->route('evenement.show', $code)
            ->with('success', 'Véhicule assigné.');
    }

    public function vehiculeDetach(string $code, int $vehicule): RedirectResponse
    {
        $event = Evenement::findOrFail($code);
        $event->vehicules()->detach($vehicule);

        return redirect()->route('evenement.show', $code)
            ->with('success', 'Véhicule désassigné.');
    }

    // ── Form lookups ─────────────────────────────────────────────────────────

    private function formLookups(): array
    {
        $categories = DB::table('categorie_evenement')
            ->orderBy('CEV_DESCRIPTION')
            ->pluck('CEV_DESCRIPTION', 'CEV_CODE');

        $groupedTypes = DB::table('type_evenement as te')
            ->where('te.TE_CODE', '<>', 'MC')
            ->orderBy('te.TE_LIBELLE')
            ->get(['te.TE_CODE', 'te.TE_LIBELLE', 'te.CEV_CODE'])
            ->groupBy('CEV_CODE')
            ->map(fn($types, $cev) => [
                'label' => $categories[$cev] ?? $cev,
                'types' => $types,
            ])
            ->sortBy('label');

        $sections = Section::orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        $chefs = Personnel::where('P_OLD_MEMBER', 0)
            ->where('P_STATUT', '!=', 'EXT')
            ->orderBy('P_NOM')
            ->orderBy('P_PRENOM')
            ->get(['P_ID', 'P_NOM', 'P_PRENOM', 'P_SECTION']);

        return [$groupedTypes, $sections, $chefs];
    }

    // ── Equipe member / material management ──────────────────────────────────

    public function equipeAddParticipant(Request $request, string $code, int $ee): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate(['P_ID' => ['required', 'integer', 'exists:pompier,P_ID']]);

        DB::table('evenement_participation')
            ->where('E_CODE', $code)
            ->where('P_ID', $validated['P_ID'])
            ->update(['EE_ID' => $ee]);

        return back()->with('success', 'Participant ajouté à l\'équipe.');
    }

    public function equipeAddMateriel(Request $request, string $code, int $ee): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate([
            'MA_ID' => ['required', 'integer', 'exists:materiel,MA_ID'],
            'EM_NB' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $existing = DB::table('evenement_materiel')
            ->where('E_CODE', $code)
            ->where('MA_ID', $validated['MA_ID'])
            ->first();

        if ($existing) {
            DB::table('evenement_materiel')
                ->where('E_CODE', $code)
                ->where('MA_ID', $validated['MA_ID'])
                ->update(['EE_ID' => $ee, 'EM_NB' => $validated['EM_NB'] ?? $existing->EM_NB]);
        } else {
            DB::table('evenement_materiel')->insert([
                'E_CODE' => $code,
                'MA_ID'  => $validated['MA_ID'],
                'EM_NB'  => $validated['EM_NB'] ?? 1,
                'EE_ID'  => $ee,
            ]);
        }

        return back()->with('success', 'Matériel ajouté à l\'équipe.');
    }

    // ── Participant team quick-assign ────────────────────────────────────────

    public function participantTeam(Request $request, string $code, int $pid): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate(['EE_ID' => ['nullable', 'integer']]);

        DB::table('evenement_participation')
            ->where('E_CODE', $code)
            ->where('P_ID', $pid)
            ->update(['EE_ID' => $validated['EE_ID'] ?: null]);

        return back()->with('success', 'Équipe mise à jour.');
    }

    // ── Matériel assignment ───────────────────────────────────────────────────

    public function materielAttach(Request $request, string $code): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate([
            'MA_ID' => ['required', 'integer', 'exists:materiel,MA_ID'],
            'EM_NB' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'EE_ID' => ['nullable', 'integer'],
        ]);

        $already = DB::table('evenement_materiel')
            ->where('E_CODE', $code)
            ->where('MA_ID', $validated['MA_ID'])
            ->exists();

        if ($already) {
            return back()->with('error', 'Ce matériel est déjà assigné à cette activité.');
        }

        DB::table('evenement_materiel')->insert([
            'E_CODE' => $code,
            'MA_ID'  => $validated['MA_ID'],
            'EM_NB'  => $validated['EM_NB'] ?? 1,
            'EE_ID'  => $validated['EE_ID'] ?: null,
        ]);

        return back()->with('success', 'Matériel assigné.');
    }

    public function materielUpdateQty(Request $request, string $code, int $ma): RedirectResponse
    {
        Evenement::findOrFail($code);

        $validated = $request->validate(['EM_NB' => ['required', 'integer', 'min:1', 'max:9999']]);

        DB::table('evenement_materiel')
            ->where('E_CODE', $code)
            ->where('MA_ID', $ma)
            ->update(['EM_NB' => $validated['EM_NB']]);

        return back()->with('success', 'Quantité mise à jour.');
    }

    public function materielDetach(string $code, int $ma): RedirectResponse
    {
        Evenement::findOrFail($code);

        DB::table('evenement_materiel')
            ->where('E_CODE', $code)
            ->where('MA_ID', $ma)
            ->delete();

        return back()->with('success', 'Matériel retiré.');
    }

    // ── Exports ───────────────────────────────────────────────────────────────

    public function exportParticipants(string $code)
    {
        $event = Evenement::findOrFail($code);

        $participants = DB::table('evenement_participation as ep')
            ->join('pompier as p', 'ep.P_ID', '=', 'p.P_ID')
            ->leftJoin('type_participation as tp', 'tp.TP_ID', '=', 'ep.TP_ID')
            ->leftJoin('evenement_equipe as ee', function ($j) use ($code) {
                $j->on('ee.EE_ID', '=', 'ep.EE_ID')->where('ee.E_CODE', '=', $code);
            })
            ->where('ep.E_CODE', $code)
            ->orderBy('p.P_NOM')
            ->orderBy('p.P_PRENOM')
            ->select(
                'p.P_NOM', 'p.P_PRENOM', 'p.P_GRADE',
                'tp.TP_LIBELLE', 'ee.EE_NAME',
                'ep.EP_COMMENT', 'ep.EP_ABSENT'
            )
            ->get();

        $columns = [
            ['Nom',         fn($p) => strtoupper($p->P_NOM)],
            ['Prénom',      fn($p) => ucfirst(mb_strtolower($p->P_PRENOM))],
            ['Grade',       fn($p) => $p->P_GRADE ?? ''],
            ['Fonction',    fn($p) => $p->TP_LIBELLE ?? ''],
            ['Équipe',      fn($p) => $p->EE_NAME ?? ''],
            ['Commentaire', fn($p) => $p->EP_COMMENT ?? ''],
            ['Absent',      fn($p) => $p->EP_ABSENT ? 'Oui' : ''],
        ];

        return (new TableExportService())->toXlsx(
            $columns,
            $participants,
            'Activite_' . $event->E_CODE . '_Participants_' . date('Ymd'),
            ['sheetTitle' => 'Participants', 'freezeHeader' => true]
        );
    }

    public function exportIcal(string $code): Response
    {
        $event = Evenement::with('horaires')->findOrFail($code);

        $vevents = [];
        foreach ($event->horaires as $h) {
            $startDate = Carbon::parse($h->EH_DATE_DEBUT);
            $endDate   = Carbon::parse($h->EH_DATE_FIN ?? $h->EH_DATE_DEBUT);

            $hasTime = $h->EH_DEBUT && substr((string) $h->EH_DEBUT, 0, 5) !== '00:00';

            if ($hasTime) {
                [$sh, $sm] = explode(':', substr((string) $h->EH_DEBUT, 0, 5));
                $startDate->setTime((int) $sh, (int) $sm)->timezone('Europe/Paris');
                if ($h->EH_FIN) {
                    [$eh, $em] = explode(':', substr((string) $h->EH_FIN, 0, 5));
                    $endDate->setTime((int) $eh, (int) $em)->timezone('Europe/Paris');
                }
            }

            $vevents[] = [
                'summary'     => $event->E_LIBELLE ?? $event->E_CODE,
                'location'    => $event->E_LIEU ?? '',
                'description' => $event->E_COMMENT ?? '',
                'uid'         => 'ob-evt-' . $event->E_CODE . '-' . $h->EH_ID . '@' . request()->getHost(),
                'allDay'      => !$hasTime,
                'dtstart'     => $startDate,
                'dtend'       => $hasTime ? $endDate : $endDate->addDay(),
            ];
        }

        return (new ICalExportService())->toResponse(
            config('app.name'),
            $vevents,
            'activite-' . $event->E_CODE
        );
    }

    // ── Shared validation ────────────────────────────────────────────────────

    private function validateEventRequest(Request $request, bool $isCreate): array
    {
        return $request->validate([
            'TE_CODE'             => ['required', 'string', 'max:10'],
            'E_LIBELLE'           => ['required', 'string', 'max:60'],
            'E_LIEU'              => ['nullable', 'string', 'max:50'],
            'E_ADDRESS'           => ['nullable', 'string', 'max:255'],
            'E_LIEU_RDV'          => ['nullable', 'string', 'max:150'],
            'E_HEURE_RDV'         => ['nullable', 'date_format:H:i'],
            'S_ID'                => ['required', 'integer'],
            'E_NB'                => ['nullable', 'integer', 'min:0', 'max:9999'],
            'E_CHEF'              => ['nullable', 'integer'],
            'E_TEL'               => ['nullable', 'string', 'max:15'],
            'horaires'                     => ['required', 'array', 'min:1'],
            'horaires.*.EH_DATE_DEBUT'     => ['required', 'date'],
            'horaires.*.EH_DATE_FIN'       => ['nullable', 'date'],
            'horaires.*.EH_DEBUT'          => ['nullable', 'date_format:H:i'],
            'horaires.*.EH_FIN'            => ['nullable', 'date_format:H:i'],
            'E_COMMENT'           => ['nullable', 'string', 'max:5000'],
            'E_CONSIGNES'         => ['nullable', 'string', 'max:500'],
            'E_CONTACT_LOCAL'     => ['nullable', 'string', 'max:50'],
            'E_CONTACT_TEL'       => ['nullable', 'string', 'max:20'],
            'E_WHATSAPP'          => ['nullable', 'string', 'max:30'],
            'E_WEBEX_URL'         => ['nullable', 'string', 'max:500'],
            'E_WEBEX_PIN'         => ['nullable', 'string', 'max:20'],
            'E_WEBEX_START'       => ['nullable', 'date_format:H:i'],
            'E_AUTOCLOSE_BEFORE'  => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
    }
}
