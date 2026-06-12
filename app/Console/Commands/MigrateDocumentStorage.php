<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Relocate library files from the legacy tree
 * ({legacy_root}/user-data/files_section/…) into the canonical app storage
 * (storage/app/private/documents/{S_ID}/{DF_ID}/…). Idempotent: a file already
 * at the canonical path is skipped. Run once after deploying the storage move.
 */
class MigrateDocumentStorage extends Command
{
    protected $signature = 'documents:migrate-storage {--dry-run : List what would move without copying}';

    protected $description = 'Move document-library files from the legacy folder into storage/app/private/documents';

    public function handle(DocumentService $documents): int
    {
        $dry = (bool) $this->option('dry-run');
        $moved = 0;
        $missing = 0;

        Document::query()->select(['D_ID', 'S_ID', 'DF_ID', 'D_NAME'])->lazy()->each(
            function (Document $d) use ($documents, $dry, &$moved, &$missing) {
                $sectionId = (int) $d->S_ID;
                $folderId = (int) $d->DF_ID;
                $name = (string) $d->D_NAME;

                $canonical = $documents->filePath($sectionId, $folderId, $name);
                if (File::exists($canonical)) {
                    return; // already migrated
                }

                $legacy = $documents->legacyFilePath($sectionId, $folderId, $name);
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

        $this->info(($dry ? 'Would move' : 'Moved').": {$moved} file(s); {$missing} with no source file on disk.");

        return self::SUCCESS;
    }
}
