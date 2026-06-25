<?php

namespace App\Services;

use App\Exceptions\UploadRejectedException;
use App\Support\ClamavScanner;
use App\Support\ClamavUnavailableException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Cross-cutting safety gate for every file upload in the app.
 *
 * Two layers, both administrable from Sécurité ▸ Renforcement:
 *  1. Validation / MIME hardening — size, extension whitelist, a hard forbidden
 *     extension/magic-byte blocklist, and (when enabled) a check that the
 *     declared extension matches the real MIME type detected from the bytes.
 *  2. Malware scanning — streams the file to a clamd daemon when scanning is on.
 *
 * Callers use {@see assertSafe()} (validate + scan). On rejection an
 * {@see UploadRejectedException} is thrown, which controllers turn into a normal
 * validation error on the upload field.
 */
class UploadSecurityService
{
    public function __construct(private readonly SecuritySettingService $settings) {}

    /**
     * Full gate: validate constraints then scan for malware. Use this from
     * upload sites.
     *
     * @param  list<string>  $allowedExtensions  lower-case, no dot
     * @param  int  $maxKb  per-feature size ceiling in kilobytes
     *
     * @throws UploadRejectedException
     */
    public function assertSafe(UploadedFile $file, array $allowedExtensions, int $maxKb, string $field = 'file'): void
    {
        $this->validate($file, $allowedExtensions, $maxKb, $field);
        $this->scan($file, $field);
    }

    /**
     * Structural validation: file integrity, size, extension whitelist, the
     * global forbidden list, and MIME/extension consistency.
     *
     * @param  list<string>  $allowedExtensions
     *
     * @throws UploadRejectedException
     */
    public function validate(UploadedFile $file, array $allowedExtensions, int $maxKb, string $field = 'file'): void
    {
        if (! $file->isValid()) {
            throw new UploadRejectedException(__('uploads.invalid'), $field);
        }

        // Cap against both the per-feature limit and the absolute ceiling.
        $maxKb = min($maxKb, (int) config('uploads.absolute_max_kb'));
        if ($file->getSize() > $maxKb * 1024) {
            throw new UploadRejectedException(
                __('uploads.too_large', ['max' => $this->humanSize($maxKb)]),
                $field,
            );
        }

        $parts = $this->extensionParts($file);
        $forbidden = array_map('strtolower', (array) config('uploads.forbidden_extensions'));

        // Reject if ANY extension part is forbidden (defeats x.php.png tricks).
        foreach ($parts as $part) {
            if (in_array($part, $forbidden, true)) {
                throw new UploadRejectedException(__('uploads.forbidden_type'), $field);
            }
        }

        $effectiveExt = end($parts) ?: '';
        $allowed = array_map('strtolower', $allowedExtensions);
        if ($allowed !== [] && ! in_array($effectiveExt, $allowed, true)) {
            throw new UploadRejectedException(
                __('uploads.bad_extension', ['list' => implode(', ', $allowed)]),
                $field,
            );
        }

        if ($this->mimeHardeningEnabled()) {
            $this->assertNoDangerousMagicBytes($file, $field);
            $this->assertMimeMatchesExtension($file, $effectiveExt, $field);
        }
    }

    /**
     * Scan the file with ClamAV when scanning is enabled. A connectivity failure
     * follows the fail_open config; a positive detection always rejects.
     *
     * @throws UploadRejectedException
     */
    public function scan(UploadedFile $file, string $field = 'file'): void
    {
        if (! $this->settings->bool('sec_upload_scan_enabled')) {
            return;
        }

        $scanner = new ClamavScanner(
            $this->settings->string('sec_clamav_host'),
            $this->settings->int('sec_clamav_port'),
            (int) config('uploads.clamav.timeout', 30),
        );

        try {
            $threat = $scanner->scan($file->getRealPath());
        } catch (ClamavUnavailableException $e) {
            Log::warning('Upload malware scan skipped: '.$e->getMessage());

            if (config('uploads.clamav.fail_open', true)) {
                return;
            }

            throw new UploadRejectedException(__('uploads.scan_unavailable'), $field);
        }

        if ($threat !== null) {
            throw new UploadRejectedException(
                __('uploads.malware_detected', ['threat' => $threat]),
                $field,
            );
        }
    }

    private function mimeHardeningEnabled(): bool
    {
        // SecuritySettingService falls back to the seeded default (on) when the
        // row is absent, so a plain bool read is sufficient.
        return $this->settings->bool('sec_upload_mime_hardening');
    }

    /** @return list<string> lower-case extension parts, e.g. ["php","png"] */
    private function extensionParts(UploadedFile $file): array
    {
        $name = $file->getClientOriginalName();
        $segments = array_slice(explode('.', $name), 1);

        return array_values(array_filter(array_map(
            fn ($s) => strtolower(trim($s)),
            $segments,
        )));
    }

    private function assertNoDangerousMagicBytes(UploadedFile $file, string $field): void
    {
        $handle = @fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return;
        }

        $head = bin2hex((string) fread($handle, 8));
        fclose($handle);

        foreach ((array) config('uploads.magic_byte_blocklist') as $signature) {
            if (str_starts_with($head, strtolower($signature))) {
                throw new UploadRejectedException(__('uploads.dangerous_content'), $field);
            }
        }
    }

    private function assertMimeMatchesExtension(UploadedFile $file, string $ext, string $field): void
    {
        if ($ext === '') {
            return;
        }

        // guessExtension() maps the real (finfo) MIME back to a canonical
        // extension; compare in a small equivalence-aware way. When finfo cannot
        // positively identify the bytes it yields application/octet-stream ('bin'):
        // don't block on that — the extension whitelist and the magic-byte
        // blocklist already gate genuinely dangerous content.
        $guessed = strtolower((string) $file->guessExtension());
        if ($guessed === '' || $guessed === 'bin') {
            return;
        }

        $equivalents = [
            'jpg' => ['jpg', 'jpeg'],
            'jpeg' => ['jpg', 'jpeg'],
            'tif' => ['tif', 'tiff'],
            'tiff' => ['tif', 'tiff'],
            'htm' => ['htm', 'html'],
            'html' => ['htm', 'html'],
            // OOXML / ODF documents are ZIP containers — finfo reports them as zip.
            'docx' => ['docx', 'zip'],
            'xlsx' => ['xlsx', 'zip'],
            'pptx' => ['pptx', 'zip'],
            'odt' => ['odt', 'zip'],
            'pps' => ['pps', 'ppt', 'zip'],
            // Legacy OLE formats are reported as a generic container by finfo.
            'doc' => ['doc', 'xls', 'ppt'],
            'xls' => ['xls', 'doc', 'ppt'],
            'ppt' => ['ppt', 'doc', 'xls'],
        ];

        $accepted = $equivalents[$ext] ?? [$ext];
        if (! in_array($guessed, $accepted, true)) {
            throw new UploadRejectedException(
                __('uploads.mime_mismatch', ['declared' => $ext, 'actual' => $guessed]),
                $field,
            );
        }
    }

    private function humanSize(int $kb): string
    {
        return $kb >= 1024
            ? round($kb / 1024, 1).' MB'
            : $kb.' KB';
    }
}
