<?php
/**
 * CLI class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

use AI_Logger\Handler\Post_Handler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use WP_CLI;

use function Mantle\Support\Helpers\collect;

// phpcs:disable WordPressVIPMinimum.Classes.RestrictedExtendClasses.wp_cli

if ( ! class_exists( 'WP_CLI_Command' ) ) {
	return;
}

/**
 * AI_Logger CLI Command
 *
 * Cannot extend `WPCOM_VIP_CLI_Command` since this plugin can run
 * outside the context of a VIP site.
 */
final class CLI {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		WP_CLI::add_command( 'ai-logger cleanup', [ $this, 'cleanup' ] );
		WP_CLI::add_command( 'ai-logger display-object', [ $this, 'display_object' ] );
		WP_CLI::add_command( 'ai-logger display', [ $this, 'display' ] );
		WP_CLI::add_command( 'ai-logger generate-for-object', [ $this, 'generate_for_object' ] );
		WP_CLI::add_command( 'ai-logger generate', [ $this, 'generate' ] );
	}

	/**
	 * Display the site-wide log.
	 *
	 * @synopsis [--count=<value>] [--offset=<value>] [--log-context=<value>] [--level=<value>]
	 *
	 * @param array $args Arguments for the command.
	 * @param array $assoc_args Associated flags for the command.
	 */
	public function display( $args, $assoc_args ): int {
		$assoc_args = \wp_parse_args(
			$assoc_args,
			[
				'count'  => 50,
				'offset' => 0,
			]
		);

		$tax_query = [
			'relation' => 'AND',
		];

		if ( ! empty( $assoc_args['log-context'] ) ) {
			$tax_query[] = [
				'taxonomy' => Post_Handler::TAXONOMY_LOG_CONTEXT,
				'field'    => 'slug',
				'terms'    => explode( ',', $assoc_args['log-context'] ),
			];
		}

		if ( ! empty( $assoc_args['level'] ) ) {
			$tax_query[] = [
				'taxonomy' => Post_Handler::TAXONOMY_LOG_LEVEL,
				'field'    => 'slug',
				'terms'    => explode( ',', $assoc_args['level'] ),
			];
		}

		$logs = get_posts( [ // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
			'fields'           => 'ids',
			'offset'           => (int) $assoc_args['offset'],
			'order'            => 'DESC',
			'orderby'          => 'date',
			'post_type'        => Post_Handler::POST_TYPE,
			'posts_per_page'   => (int) $assoc_args['count'],
			'suppress_filters' => false,
			'tax_query'        => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		] );

		if ( empty( $logs ) ) {
			WP_CLI::line( 'No logs found.' );

			return 0;
		}

		$logs = collect( $logs )->map( function ( int $log_id ): ?array {
			$record = get_post_meta( $log_id, '_logger_record', true );

			if ( ! $record ) {
				return null;
			}

			return [
				'context'    => $record['context']['context'] ?? '',
				'message'    => $record['message'] ?? '',
				'level_name' => $record['level_name'] ?? '',
				'datetime'   => isset( $record['datetime'] ) ? $record['datetime']->setTimezone( wp_timezone() )->format( 'm/d/Y H:i:s' ) : null,
			];
		} )->filter()->values()->all();

		WP_CLI\Utils\format_items(
			'table',
			array_map(
				fn ( array $log ) => [
					'level'     => $log['level_name'],
					'message'   => $log['message'],
					'context'   => $log['context'],
					'timestamp' => $log['datetime'],
				],
				$logs
			),
			[
				'level',
				'message',
				'context',
				'timestamp',
			]
		);

		return 0;
	}

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
	public function display_object( $args, $assoc_args ) {
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
				function ( $log ) {
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
	 * @synopsis [--count=<value>] [--log-context=<value>]
	 *
	 * @param array $args Arguments for the command.
	 * @param array $assoc_args Associated flags for the command.
	 */
	public function generate( $args, $assoc_args ) {
		$assoc_args = \wp_parse_args(
			$assoc_args,
			[
				'count'       => 20,
				'log-context' => 'wp-cli generator',
			]
		);

		$logger = ai_logger()->with_handlers(
			[
				new Post_Handler(),
			]
		);

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
					'context'         => $assoc_args['log-context'],
					'example_context' => $i,
					'example_data'    => [
						'key'   => 'value',
						'key_2' => 'value_2',
						'key_3' => [
							'key' => 'value',
						],
					],
				],
			);
		}

		WP_CLI::log( 'Generated ' . $assoc_args['count'] . ' log entries.' );
	}

	/**
	 * Run the Garbage Collector.
	 */
	public function cleanup() {
		AI_Logger_Garbage_Collector::run_cleanup( false );
		WP_CLI::success( 'Cleanup complete.' );
	}
}
