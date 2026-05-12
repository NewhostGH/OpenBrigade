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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Convert all legacy tables from latin1 to utf8mb4
|--------------------------------------------------------------------------
|
| The legacy eBrigade schema was created with latin1 (ISO-8859-1). This
| migration converts every table — and the database itself — to utf8mb4
| (Unicode, full 4-byte support) with the utf8mb4_unicode_ci collation.
|
| Strategy: ALTER TABLE … CONVERT TO CHARACTER SET utf8mb4 rewrites every
| text column in-place, correctly re-encoding latin1 bytes to UTF-8.
|
*/

return new class extends Migration
{
    private const CHARSET   = 'utf8mb4';
    private const COLLATION = 'utf8mb4_unicode_ci';

    public function up(): void
    {
        $database = DB::connection()->getDatabaseName();

        // 1. Convert the database default charset
        DB::statement(sprintf(
            'ALTER DATABASE `%s` CHARACTER SET %s COLLATE %s',
            $database,
            self::CHARSET,
            self::COLLATION,
        ));

        // 2. Convert every table in the database
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $row) {
            $table = $row->$tableKey;

            DB::statement(sprintf(
                'ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s',
                str_replace('`', '``', (string) $table),
                self::CHARSET,
                self::COLLATION,
            ));
        }
    }

    public function down(): void
    {
        $database = DB::connection()->getDatabaseName();

        DB::statement(sprintf(
            'ALTER DATABASE `%s` CHARACTER SET latin1 COLLATE latin1_swedish_ci',
            $database,
        ));

        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $row) {
            $table = $row->$tableKey;

            DB::statement(sprintf(
                'ALTER TABLE `%s` CONVERT TO CHARACTER SET latin1 COLLATE latin1_swedish_ci',
                str_replace('`', '``', (string) $table),
            ));
        }
    }
};
