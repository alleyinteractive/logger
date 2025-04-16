<?php
/**
 * CLI_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

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
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		if ( empty( $record['level'] ) || empty( $record['message'] ) || empty( $record['level_name'] ) ) {
			return;
		}

		$message = "Logger [{$record['level_name']}]: {$record['message']}";

		if ( Logger::ERROR === $record['level'] ) {
			$message = \WP_CLI::colorize( '%R' . $message . '%n' );
		} elseif ( Logger::WARNING === $record['level'] ) {
			$message = \WP_CLI::colorize( '%y' . $message . '%n' );
		} elseif ( Logger::INFO === $record['level'] ) {
			$message = \WP_CLI::colorize( '%g' . $message . '%n' );
		} elseif ( Logger::DEBUG === $record['level'] ) {
			$message = \WP_CLI::colorize( '%b' . $message . '%n' );
		}

		\WP_CLI::log( $message );
	}
}
