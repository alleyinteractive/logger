<?php
/**
 * CLI_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

/**
 * WP-CLI Handler to pipe logs to the wp-cli output.
 */
class CLI_Handler implements Handler_Interface {
	/**
	 * Clear the stored log, not applicable.
	 */
	public function clear() { }

	/**
	 * Write a log to the wp-cli.
	 *
	 * @param string $level Log level {@see Psr\Log\LogLevel}.
	 * @param string $message Log message.
	 * @param array  $context Context to store.
	 */
	public function handle( string $level, string $message, array $context = [] ) {
		// Ignore if the request isn't through WP-CLI.
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		\WP_CLI::log(
			\sprintf(
				'log %s %s: %s %s',
				$level,
				\current_time( 'H:i:s' ),
				$message,
				! empty( $context ) ? '(' . \wp_json_encode( $context ) . ')' : ''
			)
		);
	}
}
