<?php
/**
 * Filter_Handler class file
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use AI_Logger\Settings;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\Handler;
use Monolog\Logger;

/**
 * Filter handler to ignore certain log messages.
 */
class Filter_Handler extends Handler {
	/**
	 * Log level for handler.
	 *
	 * @var int
	 */
	private int $level;

	/**
	 * Constructor.
	 *
	 * @param string|int $level The minimum logging level at which this handler will be triggered.
	 *
	 * @phpstan-param Level|LevelName|LogLevel::* $level
	 */
	public function __construct( $level = Logger::DEBUG ) {
		$this->level = Logger::toMonologLevel( $level );
	}

	/**
	 * Ignore a specific log message.
	 * Returns true to stop bubbling if the log message is being filtered out.
	 *
	 * @param array $record Log Record.
	 */
	public function handle( array $record ): bool {
		if ( $this->is_record_filtered( $record ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the given record will be handled by this handler.
	 *
	 * @param array $record Log Record.
	 * @return bool
	 */
	public function isHandling( array $record ): bool {
		return $record['level'] >= $this->level;
	}

	/**
	 * Check if the record matches the error message filter.
	 *
	 * @param array $record Log record.
	 * @return bool.
	 */
	protected function is_record_filtered( array $record ): bool {
		if ( empty( $record['message'] ) ) {
			return false;
		}

		// Check the messages that should be filtered out.
		$message_filters = Settings::instance()->get( 'filter_error_message' );
		if ( empty( $message_filters ) ) {
			return false;
		}

		$message_filters = explode( "\n", $message_filters );

		foreach ( (array) $message_filters as $filter ) {
			$filter = trim( $filter );

			if ( preg_match( "/{$filter}/", $record['message'] ) ) {
				return true;
			}
		}

		return false;
	}
}
