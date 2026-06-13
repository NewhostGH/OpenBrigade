<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TotpController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly TwoFactorAuthenticationProvider $totp,
    ) {}

    // ── Login challenge ────────────────────────────────────────────────────────

    /** Show the TOTP code entry form (appears after a correct password). */
    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('_totp_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.totp-challenge');
    }

    /** Verify the submitted TOTP code or a recovery code and complete login. */
    public function verifyChallenge(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('_totp_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (! $user instanceof User) {
            $request->session()->forget('_totp_user_id');

            return redirect()->route('login');
        }

        $code = (string) $request->input('code', '');
        $recoveryCode = (string) $request->input('recovery_code', '');

        if ($code !== '') {
            $secret = decrypt($user->two_factor_secret ?? '');
            if (! $this->totp->verify($secret, $code)) {
                return back()->withErrors(['code' => __('Code invalide. Veuillez réessayer.')]);
            }
        } elseif ($recoveryCode !== '') {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes ?? '[]'), true) ?? [];
            $matched = collect($codes)->first(fn ($c) => hash_equals(trim($c), trim($recoveryCode)));

            if ($matched === null) {
                return back()->withErrors(['recovery_code' => __('Code de récupération invalide.')]);
            }

            // Burn the used recovery code.
            $remaining = array_values(array_filter($codes, fn ($c) => ! hash_equals(trim($c), trim($recoveryCode))));
            $user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode($remaining)),
            ])->save();
        } else {
            return back()->withErrors(['code' => __('Veuillez saisir un code.')]);
        }

        $completedUser = $this->authService->completeTotpLogin();
        if ($completedUser === null) {
            return redirect()->route('login');
        }

        $intended = (string) $request->session()->pull('url.intended', '');

        return $intended !== ''
            ? redirect()->to($intended)
            : redirect()->route('dashboard');
    }

    // ── Self-service setup / management ───────────────────────────────────────

    /** Show the TOTP setup page with QR code (requires auth). */
    public function showSetup(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        // Provision a secret if the user doesn't have one yet.
        if (empty($user->two_factor_secret)) {
            app(EnableTwoFactorAuthentication::class)($user);
            $user->refresh();
        }

        $qrSvg = $user->twoFactorQrCodeSvg();
        $secret = decrypt($user->two_factor_secret);
        $recoveryCodes = $user->two_factor_confirmed_at
            ? json_decode(decrypt($user->two_factor_recovery_codes ?? '[]'), true)
            : [];

        return view('account.totp-setup', compact('qrSvg', 'secret', 'recoveryCodes', 'user'));
    }

    /** Confirm the TOTP setup by verifying a code from the authenticator app. */
    public function confirmSetup(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $code = (string) $request->input('code', '');
        if ($code === '') {
            return back()->withErrors(['code' => __('Veuillez saisir le code affiché par votre application.')]);
        }

        $secret = decrypt($user->two_factor_secret ?? '');
        if (! $this->totp->verify($secret, $code)) {
            return back()->withErrors(['code' => __('Code invalide. Vérifiez que l\'heure de votre appareil est correcte.')]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return redirect()->route('totp.setup')
            ->with('success', __('Authentification à deux facteurs activée.'));
    }

    /** Regenerate recovery codes. */
    public function regenerateCodes(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('totp.setup');
        }

        app(GenerateNewRecoveryCodes::class)($user);

        return redirect()->route('totp.setup')
            ->with('success', __('Codes de récupération régénérés. Conservez-les en lieu sûr.'));
    }

    /** Disable TOTP after verifying the current TOTP code. */
    public function disable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $code = (string) $request->input('code', '');
        if ($code === '') {
            return back()->withErrors(['code' => __('Saisissez votre code TOTP actuel pour désactiver.')]);
        }

        $secret = decrypt($user->two_factor_secret ?? '');
        if (! $this->totp->verify($secret, $code)) {
            return back()->withErrors(['code' => __('Code invalide.')]);
        }

        app(DisableTwoFactorAuthentication::class)($user);

        return redirect()->route('totp.setup')
            ->with('success', __('Authentification à deux facteurs désactivée.'));
    }
}
