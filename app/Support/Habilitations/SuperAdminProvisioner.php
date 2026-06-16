<?php

namespace App\Support\Habilitations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Guarantees a super-administrator always exists.
 *
 * Super-admin is the account flag `pompier.P_SUPERADMIN` (not a group). This
 * provisioner is idempotent and shared by the rebuild migration and the
 * production CoreSeeder so a fresh install — whether bootstrapped by `migrate`
 * alone or `migrate --seed` — always ends with exactly one seeded super-admin
 * account, without ever overwriting an existing one's password.
 */
class SuperAdminProvisioner
{
    /**
     * Ensure the dedicated super-admin account exists.
     *
     * @return array{created:bool, code:string, password:?string} the generated
     *                                                            plaintext password is returned ONLY when the account was just
     *                                                            created (so the caller can print it once); null otherwise.
     */
    public function ensure(): array
    {
        $code = (string) config('habilitations.superadmin_code', 'superadmin');

        $existing = DB::table('pompier')->where('P_CODE', $code)->first(['P_ID']);
        if ($existing !== null) {
            // Make sure the flag is set even if the row was tampered with.
            DB::table('pompier')->where('P_ID', $existing->P_ID)->update(['P_SUPERADMIN' => 1]);
            $this->ensureAdminMembership((int) $existing->P_ID);

            return ['created' => false, 'code' => $code, 'password' => null];
        }

        $plain = Str::password(16);
        $rootSection = $this->rootSectionId();

        $pid = DB::table('pompier')->insertGetId([
            'P_CODE' => $code,
            'P_PRENOM' => 'Super',
            'P_NOM' => 'Admin',
            'P_SEXE' => 'M',
            'P_CIVILITE' => 1,
            'P_OLD_MEMBER' => 0,
            'P_GRADE' => '',
            'P_PROFESSION' => '',
            'P_STATUT' => 'SPV',
            'P_MDP' => password_hash($plain, PASSWORD_DEFAULT),
            'P_MDP_EXPIRY' => now()->toDateString(), // force change on first login
            'P_SUPERADMIN' => 1,
            'P_SECTION' => $rootSection,
            'C_ID' => 0,
            'GP_ID' => $this->adminGroupId(),
            'GP_ID2' => 0,
            'P_EMAIL' => 'superadmin@openbrigade.local',
            'P_HIDE' => 0,
            'P_NB_CONNECT' => 0,
            'GP_FLAG1' => 0,
            'GP_FLAG2' => 0,
            'P_NOSPAM' => 0,
            'TP_ID' => 0,
            'NPAI' => 0,
            'SUSPENDU' => 0,
            'MONTANT_REGUL' => 0,
            'P_MAITRE' => 0,
        ], 'P_ID');

        $this->ensureAdminMembership((int) $pid);

        return ['created' => true, 'code' => $code, 'password' => $plain];
    }

    /** The Admin base group id (the super-admin is also a member, for legacy UIs). */
    private function adminGroupId(): int
    {
        foreach ((array) config('habilitations.base_groups', []) as $id => $def) {
            if (($def['default'] ?? null) === 'admin') {
                return (int) $id;
            }
        }

        return 0;
    }

    private function ensureAdminMembership(int $personId): void
    {
        $gid = $this->adminGroupId();
        if ($gid === 0) {
            return;
        }

        DB::table('ob_personnel_group')->insertOrIgnore([
            'person_id' => $personId,
            'group_id' => $gid,
        ]);
    }

    /**
     * Organizational root section (S_ID = 0; its own S_PARENT is the -1 virtual
     * node). Falls back to a NULL/negative-parent root for legacy installs.
     * Returns null when none exists yet.
     */
    private function rootSectionId(): ?int
    {
        $id = DB::table('section')->where('S_ID', 0)->value('S_ID')
            ?? DB::table('section')
                ->where(fn ($q) => $q->where('S_PARENT', '<', 0)->orWhereNull('S_PARENT'))
                ->orderBy('S_ID')
                ->value('S_ID');

        return $id !== null ? (int) $id : null;
    }
}
