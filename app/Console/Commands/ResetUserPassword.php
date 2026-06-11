<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password
                            {identifier? : Matricule (P_CODE) or e-mail of the account}
                            {--password= : New password (skips interactive prompt)}
                            {--force-change : Force the user to change password on next login}
                            {--unblock : Reset GP_ID to 1 if the account is blocked (GP_ID = -1)}';

    protected $description = 'Reset a user password from the command line';

    public function handle(): int
    {
        $user = $this->resolveUser();

        if ($user === null) {
            return self::FAILURE;
        }

        $this->displayAccountInfo($user);

        $blocked = (int) ($user->GP_ID ?? 0) === -1;

        if ($blocked) {
            $this->warn('⚠  This account is blocked (GP_ID = -1).');
        }

        $password = $this->resolvePassword();

        if ($password === null) {
            return self::FAILURE;
        }

        $forceChange = $this->option('force-change')
            || $this->confirm('Force password change on next login?', true);

        $unblock = $blocked && (
            $this->option('unblock')
            || $this->confirm('Unblock the account (restore GP_ID = 1)?', true)
        );

        $fields = [
            'P_MDP' => password_hash($password, PASSWORD_DEFAULT),
            'P_MDP_EXPIRY' => $forceChange ? now()->toDateString() : null,
            'P_PASSWORD_FAILURE' => null,
        ];

        if ($unblock) {
            $fields['GP_ID'] = 1;
        }

        $user->forceFill($fields)->save();

        $name = trim(($user->P_NOM ?? '').' '.($user->P_PRENOM ?? ''));
        $this->info("Password reset for {$name} ({$user->P_CODE}).");

        if ($forceChange) {
            $this->line('  → User must change password on next login.');
        }

        if ($unblock) {
            $this->line('  → Account unblocked.');
        }

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $identifier = $this->argument('identifier')
            ?? $this->ask('Matricule (P_CODE) or e-mail');

        if (empty($identifier)) {
            $this->error('No identifier provided.');

            return null;
        }

        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'P_EMAIL' : 'P_CODE';
        $user = User::query()->where($field, $identifier)->first();

        if (! $user instanceof User) {
            $this->error("No account found for {$field} = {$identifier}.");

            return null;
        }

        return $user;
    }

    private function resolvePassword(): ?string
    {
        if ($this->option('password') !== null) {
            return $this->option('password');
        }

        $password = $this->secret('New password');

        if (empty($password)) {
            $this->error('Password cannot be empty.');

            return null;
        }

        $confirm = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match.');

            return null;
        }

        return $password;
    }

    private function displayAccountInfo(User $user): void
    {
        $name = trim(($user->P_NOM ?? '').' '.($user->P_PRENOM ?? ''));
        $lastLogin = $user->P_LAST_CONNECT?->format('Y-m-d H:i') ?? 'never';
        $failures = $user->P_PASSWORD_FAILURE ?? 0;
        $expiry = $user->P_MDP_EXPIRY ?? 'none';
        $blocked = (int) ($user->GP_ID ?? 0) === -1 ? '<fg=red>yes</>' : 'no';

        $this->table(
            ['Field', 'Value'],
            [
                ['Name',           $name],
                ['Matricule',      $user->P_CODE ?? '—'],
                ['E-mail',         $user->P_EMAIL ?? '—'],
                ['Last login',     $lastLogin],
                ['Login failures', $failures],
                ['Password expiry', $expiry],
                ['Blocked',        $blocked],
            ]
        );
    }
}

$x = ! $y;

$x = ! $y;

$x = ! $y;
