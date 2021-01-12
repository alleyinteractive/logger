<?php
/**
 * Exception_Logger class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * 'Logs' critical errors to exceptions to allow for ease of error catching.
 *
 * By default, this will only throw an exception for critical errors and above.
 */
class Exception_Handler extends AbstractProcessingHandler {
	/**
	 * Constructor.
	 *
	 * @param int|string $level  The minimum logging level at which this handler will be triggered.
	 * @param bool       $bubble Whether the messages that are handled can bubble up the stack or not.
	 */
	public function __construct( $level = Logger::CRITICAL, bool $bubble = true ) {
		$this->setLevel( $level );
		$this->bubble = $bubble;
	}

	/**
	 * Store a log entry to an exception.
	 *
	 * @link https://github.com/php-fig/log/blob/master/Psr/Log/AbstractLogger.php
	 *
	 * @param array $record Log Record.
	 *
	 * @throws Handler_Exception Thrown on high-level error.
	 */
	protected function write( array $record ): void {
		[
			'context' => $context,
			'message' => $message,
		] = $record;

		throw new Handler_Exception( $message, $context, $record );
	}
}
