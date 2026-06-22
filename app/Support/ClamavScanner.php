<?php

namespace App\Support;

use App\Services\UploadSecurityService;
use RuntimeException;

/**
 * Minimal ClamAV client speaking the clamd INSTREAM protocol over a raw socket.
 *
 * Deliberately dependency-free (no composer package): clamd's wire protocol is
 * tiny and stable. Used by {@see UploadSecurityService} to scan
 * uploads when malware scanning is enabled.
 *
 * Result of {@see scan()}:
 *  - null            → clean
 *  - string          → signature name of the detected threat
 * A failure to reach the daemon throws {@see ClamavUnavailableException}, which
 * the caller turns into fail-open/fail-closed behaviour.
 */
class ClamavScanner
{
    /** clamd default INSTREAM chunk ceiling is 1 MiB; stay safely under it. */
    private const CHUNK_SIZE = 8192;

    public function __construct(
        private readonly string $host = 'clamav',
        private readonly int $port = 3310,
        private readonly int $timeout = 30,
    ) {}

    /**
     * Scan a file on disk. Returns the threat signature name, or null if clean.
     *
     * @throws ClamavUnavailableException when the daemon cannot be reached.
     */
    public function scan(string $path): ?string
    {
        $stream = @stream_socket_client(
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $this->timeout,
        );

        if ($stream === false) {
            throw new ClamavUnavailableException(
                "clamd unreachable at {$this->host}:{$this->port} ({$errstr})"
            );
        }

        try {
            stream_set_timeout($stream, $this->timeout);
            fwrite($stream, "zINSTREAM\0");

            $handle = @fopen($path, 'rb');
            if ($handle === false) {
                throw new RuntimeException("Cannot open upload for scanning: {$path}");
            }

            try {
                while (! feof($handle)) {
                    $chunk = fread($handle, self::CHUNK_SIZE);
                    if ($chunk === false || $chunk === '') {
                        break;
                    }
                    // 4-byte network-order length prefix, then the chunk.
                    fwrite($stream, pack('N', strlen($chunk)).$chunk);
                }
            } finally {
                fclose($handle);
            }

            // Zero-length chunk terminates the stream.
            fwrite($stream, pack('N', 0));

            $response = trim((string) fgets($stream));
        } finally {
            fclose($stream);
        }

        // Responses: "stream: OK" or "stream: <Signature> FOUND".
        if (str_ends_with($response, 'FOUND')) {
            $sig = trim(str_replace(['stream:', 'FOUND'], '', $response));

            return $sig !== '' ? $sig : 'unknown';
        }

        if (str_ends_with($response, 'OK')) {
            return null;
        }

        throw new ClamavUnavailableException("Unexpected clamd response: {$response}");
    }

    /** Lightweight reachability check used by the admin "Test ClamAV" action. */
    public function ping(): bool
    {
        $stream = @stream_socket_client(
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $this->timeout,
        );

        if ($stream === false) {
            return false;
        }

        try {
            stream_set_timeout($stream, $this->timeout);
            fwrite($stream, "zPING\0");

            return trim((string) fgets($stream)) === 'PONG';
        } finally {
            fclose($stream);
        }
    }
}
