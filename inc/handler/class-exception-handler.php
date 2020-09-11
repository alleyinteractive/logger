<?php
/**
 * Exception_Logger class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Psr\Log\LogLevel;

/**
 * 'Logs' critical errors to exceptions to allow for ease of error catching.
 *
 * Logs with a log level of emergency, alert, or critical will throw
 * the `Handler_Exception` exception.
 */
class Exception_Handler implements Handler_Interface {
	/**
	 * Clear the stored log, not applicable.
	 */
	public function clear() { }

	/**
	 * Store a log entry to an exception.
	 *
	 * @param string $level Log level {@see Psr\Log\LogLevel}.
	 * @param string $message Log message.
	 * @param array  $context Context to store.
	 * @throws Handler_Exception Thrown on high-level error.
	 */
	public function handle( $level, $message, array $context = [] ) {
		if (
			in_array(
				$level,
				[
					LogLevel::EMERGENCY,
					LogLevel::ALERT,
					LogLevel::CRITICAL,
				],
				true
			)
		) {
			throw new Handler_Exception( $message, $context );
		}
	}
}
