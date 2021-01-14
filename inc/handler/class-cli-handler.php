<?php
/**
 * CLI_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * WP-CLI Handler to pipe logs to the wp-cli output.
 */
class CLI_Handler extends AbstractProcessingHandler {
	/**
	 * Write a log to the wp-cli.
	 *
	 * @link https://github.com/php-fig/log/blob/master/Psr/Log/AbstractLogger.php
	 *
	 * @param array $record Log Record.
	 */
	protected function write( array $record ): void {
		[ 'formatted' => $formatted ] = $record;

		// Ignore if the request isn't through WP-CLI.
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		\WP_CLI::log( $formatted );
	}
}
