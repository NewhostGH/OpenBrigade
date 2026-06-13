<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\PasswordPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly PasswordPolicyService $policyService,
    ) {}

    // ── Password change ────────────────────────────────────────────────────────

    public function showChangePassword(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $isExpired = $request->boolean('expired');
        $isFirstLogin = ((int) ($user->P_NB_CONNECT ?? 0)) <= 1;
        $policy = $this->policyService->policyForUser($user);

        return view('auth.change-password', compact('isExpired', 'isFirstLogin', 'policy'));
    }

    public function changePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $new1 = (string) $request->input('new1', '');
        $new2 = (string) $request->input('new2', '');
        $current = (string) $request->input('current', '');
        $isFirstLogin = ((int) ($user->P_NB_CONNECT ?? 0)) <= 1;

        if (! $isFirstLogin) {
            if (! $this->authService->verifyPassword($current, (string) $user->P_MDP)) {
                return back()->withErrors(['current' => __('Le mot de passe actuel est incorrect.')]);
            }
        }

        if ($new1 !== $new2) {
            return back()->withErrors(['new2' => __('Les deux mots de passe ne correspondent pas.')]);
        }

        $error = $this->policyService->validate($new1, (string) ($user->P_CODE ?? ''), $this->policyService->policyForUser($user));
        if ($error !== null) {
            return back()->withErrors(['new1' => $error]);
        }

        $this->authService->updatePassword($user, $new1);
        $this->logHistory('UPDMDP', $user->P_ID, $user->P_ID);

        if ($this->charteActive() && empty($user->P_ACCEPT_DATE)) {
            return redirect()->route('account.charter')
                ->with('success', __('Mot de passe modifié avec succès.'));
        }

        return redirect()->route('personnel.show', $user->P_ID)
            ->with('success', __('Mot de passe modifié avec succès.'));
    }

    // ── Charter acceptance ─────────────────────────────────────────────────────

    public function showCharter(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $acceptDate = DB::table('pompier')->where('P_ID', $user->P_ID)->value('P_ACCEPT_DATE');
        $charteMeta = $this->buildCharteMeta();
        $rgpdExists = $this->rgpdFileExists();
        $charteText = $this->loadCharterText();

        return view('auth.charter', compact('acceptDate', 'charteMeta', 'rgpdExists', 'charteText'));
    }

    public function showEditCharter(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->hasPermission(14), 403);

        $charteText = $this->loadCharterText() ?? '';
        $updatedAt = $this->charterUpdatedAt();

        return view('admin.charter-edit', compact('charteText', 'updatedAt'));
    }

    public function saveCharter(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->hasPermission(14), 403);

        $text = (string) $request->input('charte_text', '');
        $forceReaccept = $request->boolean('force_reaccept');

        $path = $this->charterTextPath();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $text);

        if ($forceReaccept) {
            DB::table('configuration')->updateOrInsert(
                ['NAME' => 'charte_updated_at'],
                ['VALUE' => now()->format('Y-m-d H:i:s'), 'HIDDEN' => 1, 'TAB' => 0, 'ORDERING' => 0],
            );
        }

        $this->logHistory('CHARTE_EDIT', $user->P_ID, $user->P_ID);

        $msg = __('Charte mise à jour.');
        if ($forceReaccept) {
            $msg .= ' '.__('Les utilisateurs devront réaccepter.');
        }

        return redirect()->route('admin.security', ['tab' => 'charter'])->with('success', $msg);
    }

    public function acceptCharter(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        DB::table('pompier')->where('P_ID', $user->P_ID)->update(['P_ACCEPT_DATE' => now()]);
        $this->logHistory('ACCEPT', $user->P_ID, $user->P_ID);

        return redirect()->route('dashboard')
            ->with('success', __("Conditions d'utilisation acceptées."));
    }

    public function rejectCharter(Request $request): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login')
            ->with('error', __("Vous avez refusé les conditions d'utilisation. Vous avez été déconnecté."));
    }

    public function resetCharter(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->hasPermission(14), 403);

        DB::table('pompier')->update(['P_ACCEPT_DATE' => null]);

        return redirect()->route('admin.security', ['tab' => 'charter'])
            ->with('success', __("Tous les utilisateurs devront de nouveau accepter les conditions d'utilisation."));
    }

    // ── Send credentials ───────────────────────────────────────────────────────

    public function showSendCredentials(Request $request, Personnel $personnel): View
    {
        /** @var User $authUser */
        $authUser = $request->user();
        abort_unless($authUser->hasPermission(9) || $authUser->hasPermission(25), 403);

        return view('auth.send-credentials', [
            'personnel' => $personnel,
            'mode' => null,
            'newPass' => null,
        ]);
    }

    public function sendCredentials(Request $request, Personnel $personnel): View
    {
        /** @var User $authUser */
        $authUser = $request->user();
        abort_unless($authUser->hasPermission(9) || $authUser->hasPermission(25), 403);

        $mode = $request->input('mode', 'manual');
        $newPass = $this->policyService->generateTemporaryPassword();

        $this->authService->resetPasswordTo($personnel->P_ID, $newPass);

        $comment = ($mode === 'auto')
            ? 'Envoi automatique à '.($personnel->P_EMAIL ?? '—')
            : 'Envoi manuel';
        $this->logHistory('REGENMDP', $authUser->P_ID, $personnel->P_ID, $comment);

        $sent = false;
        if ($mode === 'auto') {
            // TODO: COMM — send credentials email via NotificationService when the
            // communication module is implemented. The $newPass and $personnel->P_EMAIL
            // are ready; wire them up in the COMM phase.
            $sent = false;
        }

        return view('auth.send-credentials', compact('personnel', 'mode', 'newPass', 'sent'));
    }

    // ── Connected users ────────────────────────────────────────────────────────

    public function connectedUsers(): View
    {
        $daysAudit = (int) (DB::table('configuration')->where('NAME', 'days_audit')->value('VALUE') ?? 100);

        $connected = DB::table('audit as a')
            ->join('pompier as p', 'p.P_ID', '=', 'a.P_ID')
            ->join('section as s', 's.S_ID', '=', 'p.P_SECTION')
            ->select([
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_PHOTO', 'p.P_SEXE',
                's.S_CODE', 's.S_ID as sect_id',
                'a.A_DEBUT', 'a.A_FIN', 'a.A_OS', 'a.A_BROWSER', 'a.A_IP',
            ])
            ->where(function ($q) {
                $q->where('a.A_DEBUT', '>', now()->subMinutes(10))
                    ->orWhere('a.A_FIN', '>', now()->subMinutes(3));
            })
            ->whereNotNull('a.A_FIN')
            ->whereRaw('TIME_TO_SEC(TIMEDIFF(NOW(), a.A_DEBUT)) < ?', [24 * 3600 * $daysAudit])
            ->orderByDesc('a.A_DEBUT')
            ->get();

        return view('auth.connected-users', compact('connected'));
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function charteActive(): bool
    {
        return (bool) DB::table('configuration')->where('NAME', 'charte_active')->value('VALUE');
    }

    private function charterUpdatedAt(): ?string
    {
        return DB::table('configuration')->where('NAME', 'charte_updated_at')->value('VALUE');
    }

    private function charterTextPath(): string
    {
        return storage_path('app/private/charte/charte.html');
    }

    private function loadCharterText(): ?string
    {
        $path = $this->charterTextPath();

        return file_exists($path) ? (string) file_get_contents($path) : null;
    }

    private function logHistory(string $ltCode, int $actorId, int $whatId, string $complement = ''): void
    {
        DB::table('log_history')->insert([
            'P_ID' => $actorId,
            'LH_STAMP' => now(),
            'LT_CODE' => $ltCode,
            'LH_WHAT' => $whatId,
            'LH_COMPLEMENT' => $complement,
            'COMPLEMENT_CODE' => 0,
        ]);
    }

    /** @return array<string,mixed> */
    private function buildCharteMeta(): array
    {
        $rows = DB::table('configuration')
            ->whereIn('NAME', ['application_title', 'cisname', 'cisurl', 'syndicate'])
            ->pluck('VALUE', 'NAME');

        $appTitle = $rows['application_title'] ?? config('app.name');
        $cisname = $rows['cisname'] ?? config('app.name');
        $cisurl = $rows['cisurl'] ?? config('app.url');
        $syndicate = (int) ($rows['syndicate'] ?? 0);
        $nbsections = (int) DB::table('section')->where('S_INACTIVE', 0)->count();

        $site = ($appTitle !== 'eBrigade') ? $appTitle : str_replace('www.', '', $cisurl);

        if ($nbsections === 0 && $syndicate === 1) {
            $orgType = 'du syndicat';
            $memberSuffix = ' et des adhérents ';
        } elseif ($nbsections === 0) {
            $orgType = "de l'association";
            $memberSuffix = '';
        } else {
            $orgType = "du centre d'incendie et de secours";
            $memberSuffix = '';
        }

        return compact('site', 'cisname', 'orgType', 'memberSuffix', 'nbsections', 'syndicate', 'appTitle');
    }

    private function rgpdFileExists(): bool
    {
        return file_exists(storage_path('app/private/charte/charte_RGPD.pdf'));
    }
}
