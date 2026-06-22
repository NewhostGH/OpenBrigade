<?php

namespace App\Support;

use RuntimeException;

/**
 * Raised by {@see ClamavScanner} when the clamd daemon cannot be reached or
 * returns an unparseable response. Distinct from a malware hit so callers can
 * choose fail-open vs fail-closed behaviour for connectivity problems only.
 */
class ClamavUnavailableException extends RuntimeException {}
