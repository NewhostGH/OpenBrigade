<?php

namespace App\Logging;

use Monolog\Level;
use Monolog\Logger;

/**
 * Factory for the custom `database` log channel (config/logging.php).
 *
 * Returns a Monolog logger whose single handler persists records to
 * `ob_log_entry`, enriched with request/actor context. The minimum level is
 * driven by config so the runtime can raise/lower it from the admin settings.
 */
class DatabaseLogger
{
    public function __invoke(array $config): Logger
    {
        $level = Level::fromName(ucfirst($config['level'] ?? 'warning'));

        $handler = new DatabaseLogHandler($level);

        $logger = new Logger('database');
        $logger->pushProcessor(new RequestContextProcessor);
        $logger->pushHandler($handler);

        return $logger;
    }
}
