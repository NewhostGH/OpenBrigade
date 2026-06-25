<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidate the legacy `log_history` activity trail into the unified
 * `ob_log_entry` store under the `activity` canal, so all history lives in one
 * place (the legacy mechanism is retired in the application code).
 *
 * Copies rows in chunks to stay memory-safe on large production tables.
 * Idempotent guard: skips entirely if either table is missing or if the backfill
 * has already run (an `activity` row already exists).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('log_history') || ! Schema::hasTable('ob_log_entry')) {
            return;
        }

        // Don't double-import if this migration is re-run after a manual rollback.
        if (DB::table('ob_log_entry')->where('channel', 'activity')->exists()) {
            return;
        }

        // LT_CODE → human description, for richer context.
        $descriptions = Schema::hasTable('log_type')
            ? DB::table('log_type')->pluck('LT_DESCRIPTION', 'LT_CODE')
            : collect();

        DB::table('log_history')->orderBy('LH_ID')->chunkById(1000, function ($rows) use ($descriptions): void {
            $batch = [];
            foreach ($rows as $row) {
                $context = [];
                if (! empty($row->LH_WHAT) && (int) $row->LH_WHAT !== (int) ($row->P_ID ?? 0)) {
                    $context['target_p_id'] = (int) $row->LH_WHAT;
                }
                if (! empty($row->LH_COMPLEMENT)) {
                    $context['detail'] = $row->LH_COMPLEMENT;
                }
                if (isset($descriptions[$row->LT_CODE])) {
                    $context['label'] = $descriptions[$row->LT_CODE];
                }

                $batch[] = [
                    'level' => 'info',
                    'channel' => 'activity',
                    'message' => (string) ($row->LT_CODE ?? 'ACTIVITY'),
                    'context' => $context === [] ? null : json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'p_id' => $row->P_ID !== null ? (int) $row->P_ID : null,
                    'created_at' => $row->LH_STAMP,
                ];
            }

            if ($batch !== []) {
                DB::table('ob_log_entry')->insert($batch);
            }
        }, 'LH_ID');
    }

    public function down(): void
    {
        if (Schema::hasTable('ob_log_entry')) {
            DB::table('ob_log_entry')->where('channel', 'activity')->delete();
        }
    }
};
