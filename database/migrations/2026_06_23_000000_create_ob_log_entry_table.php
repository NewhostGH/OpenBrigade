<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified observability log: structured application logs, activity history and
 * captured error traces in one table.
 *
 * Rows are written by the custom Monolog `database` channel
 * (App\Logging\DatabaseLogHandler) — every Log::*, every uncaught exception
 * (severity ≥ the configured level) and every slow request lands here, enriched
 * with the acting pompier and request metadata. The same events are mirrored to
 * the file channel and, when enabled, to Sentry/GlitchTip.
 *
 * Settings (level, outputs, retention) live in the legacy `configuration` table
 * via App\Services\LoggingSettingService and are administered under
 * Administration ▸ Journal d'activité ▸ Paramètres.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_log_entry', function (Blueprint $table) {
            $table->id();

            // PSR-3 / Monolog level (debug, info, notice, warning, error,
            // critical, alert, emergency) and the logical channel it came from.
            $table->string('level', 12)->index();
            $table->string('channel', 40)->default('app')->index();

            $table->text('message');
            $table->json('context')->nullable();

            // Error trace (null for ordinary log lines).
            $table->string('exception_class')->nullable();
            $table->text('exception_message')->nullable();
            $table->longText('exception_trace')->nullable();

            // Actor + request metadata (best-effort; null for console/queue).
            $table->unsignedInteger('p_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('user_agent', 512)->nullable();

            // Basic performance metrics (populated on the `performance` channel).
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('memory_mb')->nullable();

            // Only created_at matters; entries are immutable. Indexed for the
            // viewer's default "most recent first" ordering and retention prune.
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_log_entry');
    }
};
