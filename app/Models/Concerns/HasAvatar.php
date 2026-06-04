<?php

# project: OpenBrigade

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Models\Concerns;

/**
 * Single source of truth for a person's avatar URL.
 *
 * Shared by the User (auth) and Personnel (domain) models, which both map to
 * the `pompier` table. The static helper exists so raw query-builder rows
 * (stdClass, no Eloquent instance) can resolve the exact same URL without
 * re-implementing the logic — see DashboardService duty/birthday widgets.
 */
trait HasAvatar
{
    /**
     * Avatar URL for this model instance.
     */
    public function getAvatarUrl(): string
    {
        return static::avatarUrl($this->getKey(), $this->P_PHOTO ?? null, $this->P_CIVILITE ?? null);
    }

    /**
     * Avatar URL from raw values (id + photo filename + civility code).
     *
     * @param  int|string  $id        pompier P_ID (route key)
     * @param  string|null $photo     P_PHOTO filename, empty/null when unset
     * @param  int|null    $civilite  P_CIVILITE (2 = female → girl.png, else boy.png)
     */
    public static function avatarUrl($id, ?string $photo, $civilite): string
    {
        $photo = (string) ($photo ?? '');
        if ($photo !== '') {
            return route('personnel.photo', $id) . '?v=' . substr(md5($photo), 0, 8);
        }

        return (int) $civilite === 2
            ? asset('images/girl.png')
            : asset('images/boy.png');
    }
}
