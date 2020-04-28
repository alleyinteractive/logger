<?php
/**
 * Logger class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Psr\Log\{
	AbstractLogger,
	LoggerInterface
};

/**
 * Abstract Logger
 *
 * Uses shared methods to define a consistent logger interface to use.
 * Inherited from the PSR-3 logger. The design of the log handlers is
 * based on Monolog's handlers.
 *
 * @link https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md
 */
abstract class Handler extends AbstractLogger implements LoggerInterface {
	/**
	 * Clear the stored log, not applicable.
	 */
	abstract public function clear();

	/**
	 * Store a log entry.
	 *
	 * Usage of this method directly is prohibited. Logs can be piped through using the
	 * various methods of {@see Psr\Log\AbstractLogger}.
	 *
	 * @link https://github.com/php-fig/log/blob/master/Psr/Log/AbstractLogger.php
	 *
	 * @param string $level Log level {@see Psr\Log\LogLevel}.
	 * @param string $message Log message.
	 * @param array  $context Context to store.
	 */
	abstract public function log( $level, $message, array $context = [] );
}
