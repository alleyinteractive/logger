<?php
/**
 * CLI class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

use Monolog\Logger;
use WP_CLI;

// phpcs:disable WordPressVIPMinimum.Classes.RestrictedExtendClasses.wp_cli

/**
 * AI_Logger CLI Command
 *
 * Cannot extend `WPCOM_VIP_CLI_Command` since this plugin can run
 * outside the context of a VIP site.
 */
class CLI extends \WP_CLI_Command {
	/**
	 * Display the log for a post.
	 *
	 * Default meta key is 'log'.
	 *
	 * ## OPTIONS
	 *
	 * <object_type>
	 * : Object type (post/term).
	 *
	 * <object_id>
	 * : Object ID.
	 *
	 * @synopsis <object_type> <object_id> [--meta_key=<value>]
	 * @param array $args Arguments for the command.
	 * @param array $assoc_args Associated flags for the command.
	 */
	public function display( $args, $assoc_args ) {
		[ $object_type, $object_id ] = $args;

		$assoc_args = \wp_parse_args(
			$assoc_args,
			[
				'meta_key' => 'log',
			]
		);

		$logs = \get_metadata( $object_type, $object_id, $assoc_args['meta_key'], false );

		if ( empty( $logs ) ) {
			WP_CLI::error( 'No logs found.' );
		}

		WP_CLI\Utils\format_items(
			'table',
			array_map(
				function( $log ) {
					return [
						'level'     => $log[0],
						'message'   => $log[1],
						'context'   => $log[2],
						'timestamp' => date_i18n( 'm/d/Y H:i:s', (int) $log[3] ?? '', false ),
					];
				},
				$logs
			),
			[
				'level',
				'message',
				'context',
				'timestamp',
			]
		);
	}

	/**
	 * Generate some logs for a object (used for development).
	 *
	 * ## OPTIONS
	 *
	 * <object_type>
	 * : Object type (post/term).
	 *
	 * <object_id>
	 * : Object ID.
	 *
	 * @synopsis <object_type> <object_id> [--meta_key=<value>] [--count=<value>]
	 * @param array $args Arguments for the command.
	 * @param array $assoc_args Associated flags for the command.
	 */
	public function generate_for_object( $args, $assoc_args ) {
		list ( $object_type, $object_id ) = $args;

		$assoc_args = \wp_parse_args(
			$assoc_args,
			[
				'count'    => 20,
				'meta_key' => 'log',
			]
		);

		$meta_key = $assoc_args['meta_key'] ?? '';

		$logger = new Logger( 'Log Generator' );

		if ( 'term' === $object_type ) {
			WP_CLI::log( 'Generating logs using Term_Meta_Handler' );
			$logger->setHandlers( [ new Handler\Term_Meta_Handler( Logger::DEBUG, true, $object_id, $meta_key ) ] );
		} else {
			WP_CLI::log( 'Generating logs using Post_Meta_Handler' );
			$logger->setHandlers( [ new Handler\Post_Meta_Handler( Logger::DEBUG, true, $object_id, $meta_key ) ] );
		}

		$levels = [
			LogLevel::EMERGENCY,
			LogLevel::ALERT,
			LogLevel::CRITICAL,
			LogLevel::ERROR,
			LogLevel::WARNING,
			LogLevel::NOTICE,
			LogLevel::INFO,
			LogLevel::DEBUG,
		];

		for ( $i = 0; $i < $assoc_args['count']; $i++ ) {
			$level = $levels[ array_rand( $levels ) ];
			$logger->$level(
				'Example log message: ' . ( $i + 1 ),
				[
					'context'         => 'wp-cli generator',
					'example_context' => $i,
				]
			);
		}

		WP_CLI::log( 'Generated ' . $assoc_args['count'] . ' log entries.' );
	}

	/**
	 * Generate some logs for the site-wide handler.
	 *
	 * @synopsis [--count=<value>]
	 * @param array $args Arguments for the command.
	 * @param array $assoc_args Associated flags for the command.
	 */
	public function generate_for_blog( $args, $assoc_args ) {
		$assoc_args = \wp_parse_args(
			$assoc_args,
			[
				'count' => 20,
			]
		);

		$logger = AI_Logger::instance()->get_logger();

		$levels = [
			LogLevel::EMERGENCY,
			LogLevel::ALERT,
			LogLevel::CRITICAL,
			LogLevel::ERROR,
			LogLevel::WARNING,
			LogLevel::NOTICE,
			LogLevel::INFO,
			LogLevel::DEBUG,
		];

		for ( $i = 0; $i < $assoc_args['count']; $i++ ) {
			$level = $levels[ array_rand( $levels ) ];
			$logger->$level(
				'Example log message: ' . ( $i + 1 ),
				[
					'context'         => 'wp-cli generator',
					'example_context' => $i,
				]
			);
		}

		WP_CLI::log( 'Generated ' . $assoc_args['count'] . ' log entries.' );
	}
}
