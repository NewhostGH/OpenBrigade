<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Personnel;
use App\Services\DocumentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * One-shot command to relocate all user-uploaded files from their legacy/public
 * locations into the canonical private storage tree. Idempotent: a file already
 * at the canonical path is skipped. Run once after deploying on a new environment.
 *
 * Covers:
 *  - Document library: {legacy_root}/user-data/files_section/… → storage/app/private/documents/…
 *  - Personnel photos: public/images/user-specific/profile_pictures/…   → storage/app/private/profile_pictures/…
 */
class MigrateStorage extends Command
{
    protected $signature = 'storage:migrate {--dry-run : List what would move without copying}';

    protected $description = 'Move all user-uploaded files from legacy/public locations into private storage';

    public function handle(DocumentService $documents): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->info('── Document library ──────────────────────────────────────────');
        $this->migrateDocuments($documents, $dry);

        $this->info('── Personnel photos (profile_pictures) ─────────────────────────────────');
        $this->migrateTrombi($dry);

        return self::SUCCESS;
    }

    private function migrateDocuments(DocumentService $documents, bool $dry): void
    {
        $moved = 0;
        $missing = 0;

        Document::query()->select(['D_ID', 'S_ID', 'DF_ID', 'D_NAME'])->lazy()->each(
            function (Document $d) use ($documents, $dry, &$moved, &$missing) {
                $canonical = $documents->filePath((int) $d->S_ID, (int) $d->DF_ID, (string) $d->D_NAME);
                if (File::exists($canonical)) {
                    return;
                }

                $legacy = $documents->legacyFilePath((int) $d->S_ID, (int) $d->DF_ID, (string) $d->D_NAME);
                if (! File::exists($legacy)) {
                    $missing++;

                    return;
                }

                $this->line(($dry ? '[dry] ' : '').'move '.$legacy.' → '.$canonical);
                if (! $dry) {
                    File::ensureDirectoryExists(dirname($canonical));
                    File::copy($legacy, $canonical);
                }
                $moved++;
            }
        );

        $this->info(($dry ? 'Would move' : 'Moved').": {$moved} document(s); {$missing} with no source on disk.");
    }

    private function migrateTrombi(bool $dry): void
    {
        $moved = 0;
        $missing = 0;
        $canonical_dir = storage_path('app/private/profile_pictures');

        Personnel::query()->whereNotNull('P_PHOTO')->where('P_PHOTO', '!=', '')->select(['P_ID', 'P_PHOTO'])->lazy()->each(
            function (Personnel $p) use ($dry, $canonical_dir, &$moved, &$missing) {
                $filename = basename((string) $p->P_PHOTO);
                $canonical = $canonical_dir.'/'.$filename;

                if (File::exists($canonical)) {
                    return;
                }

                $sources = [
                    public_path('images/user-specific/profile_pictures/'.$filename),
                    base_path('archive/legacy_app/images/user-specific/profile_pictures/'.$filename),
                ];

                foreach ($sources as $src) {
                    if (File::exists($src)) {
                        $this->line(($dry ? '[dry] ' : '').'move '.$src.' → '.$canonical);
                        if (! $dry) {
                            File::ensureDirectoryExists($canonical_dir);
                            File::copy($src, $canonical);
                        }
                        $moved++;

                        return;
                    }
                }

                $missing++;
            }
        );

        $this->info(($dry ? 'Would move' : 'Moved').": {$moved} photo(s); {$missing} with no source on disk.");
    }
}
