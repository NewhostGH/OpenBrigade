<?php

namespace App\Services;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * Renders arbitrary payloads as QR codes.
 *
 * SVG output is preferred: it is resolution-independent, embeds inline without a
 * separate image request, and needs no gd/imagick extension. The single source
 * of the encoding parameters (size, margin, error-correction level) lives here so
 * every QR code across the app looks identical.
 */
class QrCodeService implements ServiceInterface
{
    private const SIZE = 256;

    private const MARGIN = 8;

    /** Render the payload as a standalone SVG document string. */
    public function svg(string $data): string
    {
        return (new SvgWriter)
            ->write($this->qrCode($data), null, null, [
                // Inline embedding in a Blade view: strip the XML prolog.
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
            ])
            ->getString();
    }

    /** Render the payload as a `data:` URI suitable for an <img src> attribute. */
    public function svgDataUri(string $data): string
    {
        return (new SvgWriter)
            ->write($this->qrCode($data))
            ->getDataUri();
    }

    private function qrCode(string $data): QrCode
    {
        return new QrCode(
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: self::SIZE,
            margin: self::MARGIN,
        );
    }
}
