<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Controllers;

use App\Services\NotificationService;
use App\Services\PasswordPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Self-service password reset (guest-accessible).
 *
 * Flow:
 *   1. GET  /password/reset          — show the request form (matricule or email)
 *   2. POST /password/reset          — validate identity, store token in `demande`, send email
 *   3. GET  /password/reset/{token}  — confirm token, generate new temporary password, send it
 */
class PasswordResetController extends Controller
{
    public function __construct(
        private readonly PasswordPolicyService $policyService,
        private readonly NotificationService $notificationService,
    ) {}

    public function showRequestForm(): View
    {
        return view('auth.password-reset');
    }

    public function sendResetToken(Request $request): RedirectResponse|View
    {
        $recovery = trim((string) $request->input('recovery', ''));

        if ($recovery === '') {
            return back()->withErrors(['recovery' => __('Veuillez saisir votre identifiant ou adresse e-mail.')]);
        }

        $isEmail = filter_var($recovery, FILTER_VALIDATE_EMAIL) !== false;
        $field = $isEmail ? 'P_EMAIL' : 'P_CODE';

        $person = DB::table('pompier')
            ->where($field, $recovery)
            ->where('GP_ID', '>=', 0)
            ->where('GP_ID2', '>=', 0)
            ->whereNull('P_FIN')
            ->first(['P_ID', 'P_NOM', 'P_PRENOM', 'P_EMAIL']);

        if ($person === null) {
            // Return the same success message to avoid user enumeration.
            return view('auth.password-reset', ['submitted' => true]);
        }

        if (empty($person->P_EMAIL)) {
            return view('auth.password-reset', ['submitted' => true]);
        }

        $secret = $this->generateSecret();

        DB::table('demande')->where('P_ID', $person->P_ID)->where('D_TYPE', 'password')->delete();
        DB::table('demande')->insert([
            'P_ID' => $person->P_ID,
            'D_TYPE' => 'password',
            'D_SECRET' => $secret,
            'D_DATE' => now(),
        ]);

        $appName = config('app.name');
        $resetUrl = route('password.reset', ['token' => $secret]);
        $body = 'Bonjour '.ucfirst((string) $person->P_PRENOM).",\n\n"
            ."Vous avez demandé un renouvellement de votre mot de passe {$appName}.\n"
            ."Confirmez cette demande en cliquant sur le lien suivant :\n\n"
            ."{$resetUrl}\n\n"
            ."Si vous n'avez pas fait cette demande, ignorez ce message.";

        $this->notificationService->sendEmail(
            (string) $person->P_EMAIL,
            "Renouvellement de mot de passe — {$appName}",
            $body,
        );

        return view('auth.password-reset', ['submitted' => true]);
    }

    public function confirmToken(Request $request, string $token): View
    {
        $row = DB::table('demande as d')
            ->join('pompier as p', 'p.P_ID', '=', 'd.P_ID')
            ->where('d.D_TYPE', 'password')
            ->where('d.D_SECRET', $token)
            ->where('d.D_DATE', '>=', now()->subDay())
            ->whereNull('p.P_FIN')
            ->first(['d.P_ID', 'p.P_PRENOM', 'p.P_NOM', 'p.P_EMAIL']);

        if ($row === null) {
            return view('auth.password-reset-confirm', ['valid' => false, 'newPass' => null]);
        }

        $newPass = $this->policyService->generateTemporaryPassword();
        $hash = password_hash($newPass, PASSWORD_DEFAULT);

        DB::table('pompier')->where('P_ID', $row->P_ID)->update([
            'P_MDP' => $hash,
            'P_PASSWORD_FAILURE' => null,
            'P_MDP_EXPIRY' => now()->format('Y-m-d'),
        ]);

        DB::table('demande')->where('P_ID', $row->P_ID)->where('D_TYPE', 'password')->delete();

        if (! empty($row->P_EMAIL)) {
            $appName = config('app.name');
            $body = 'Bonjour '.ucfirst((string) $row->P_PRENOM).",\n\n"
                ."Votre nouveau mot de passe temporaire est :\n\n"
                ."    {$newPass}\n\n"
                .'Connectez-vous et changez-le dès votre prochaine connexion.';

            $this->notificationService->sendEmail(
                (string) $row->P_EMAIL,
                "Nouveau mot de passe — {$appName}",
                $body,
            );
        }

        return view('auth.password-reset-confirm', ['valid' => true, 'newPass' => $newPass]);
    }

    private function generateSecret(): string
    {
        return bin2hex(random_bytes(16));
    }
}
