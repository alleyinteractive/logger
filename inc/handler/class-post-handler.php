<?php
/**
 * Post_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Post Log Handler
 *
 * Writes logs to a custom post type that allows logs to be viewed
 * across the site.
 */
class Post_Handler extends AbstractProcessingHandler implements Handler_Interface {
	/**
	 * Post type to log to.
	 *
	 * @var string
	 */
	public const POST_TYPE = 'ai_log';

	/**
	 * Log Level Taxonomy
	 *
	 * @var string
	 */
	public const TAXONOMY_LOG_LEVEL = 'ai_log_level';

	/**
	 * Log Context Taxonomy
	 *
	 * @var string
	 */
	public const TAXONOMY_LOG_CONTEXT = 'ai_log_context';

	/**
	 * Queue of log messages.
	 *
	 * @var array
	 */
	protected $queue = [];

	/**
	 * Originating site ID.
	 *
	 * @var int
	 */
	protected $original_site_id;

	/**
	 * The time limit that this logger should wait before
	 * attempting to insert another UNIQUE log entry in seconds
	 *
	 * @var int
	 * @access protected
	 */
	protected $throttle_limit;

	/**
	 * Constructor.
	 *
	 * @param int|string $level  The minimum logging level at which this handler will be triggered.
	 * @param bool       $bubble Whether the messages that are handled can bubble up the stack or not.
	 */
	public function __construct( $level = Logger::DEBUG, bool $bubble = true ) {
		parent::__construct( $level, $bubble );

		$this->throttle_limit   = (int) apply_filters( 'ai_logger_throttle_limit', MINUTE_IN_SECONDS * 15 );
		$this->original_site_id = \get_current_blog_id();

		\add_action( 'shutdown', [ $this, 'process_queue_shutdown' ] );
	}

	/**
	 * Clear the stored log, not supported.
	 */
	public function clear() {
		// Not supported by this handler.
	}

	/**
	 * Store a log entry.
	 *
	 * Usage of this method directly is prohibited. Logs can be piped through using the
	 * various methods of {@see Psr\Log\AbstractLogger}.
	 *
	 * @link https://github.com/php-fig/log/blob/master/Psr/Log/AbstractLogger.php
	 *
	 * @param array $record Log Record.
	 */
	protected function write( array $record ): void {
		$user = wp_get_current_user();

		// Capture the stack trace.
		$record['extra']['backtrace'] = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

		if ( $user ) {
			$record['extra']['user'] = [
				'ID'         => $user->ID,
				'user_login' => $user->user_login,
				'user_email' => $user->user_email,
			];
		}

		/**
		 * Filter the log record.
		 *
		 * @param array $record Log record.
		 */
		$record = (array) apply_filters( 'ai_logger_log_record', $record );

		$transient_key = 'ai_log_' . md5( $record['message'] . $record['channel'] );

		$this->queue[ $transient_key ] = $record;

		if ( ! $this->should_write_on_shutdown() ) {
			$this->process_queue();
		}
	}

	/**
	 * Process the queue of log messages.
	 */
	public function process_queue() {
		if ( \get_current_blog_id() !== $this->original_site_id ) {
			return;
		}

		// Loop through the array of possible log entries.
		foreach ( $this->queue as $transient_key => $log ) {
			// Determine if this insert should actually write to the DB.
			if ( ! $this->insert_permitted( $transient_key, $log ) ) {
				continue;
			}

			$level = $log['level_name'];

			// Log message content.
			$content = [
				$log['formatted'],
				/* translators: 1: Log Channel */
				sprintf( __( 'Log Channel: %s', 'ai-logger' ), $log['channel'] ),
				wp_json_encode( $log['context'], JSON_PRETTY_PRINT ),
			];

			$log_post_id = \wp_insert_post(
				[
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_content'   => implode( PHP_EOL, $content ),
					'post_status'    => 'publish',
					'post_title'     => $log['message'],
					'post_type'      => static::POST_TYPE,
				]
			);

			if ( ! empty( $log_post_id ) ) {
				\update_post_meta( $log_post_id, '_logger_record', $log );

				$this->assign_terms( $log_post_id, $level, static::TAXONOMY_LOG_LEVEL );

				$log_context = $log['context']['context'] ?? '';
				if ( ! empty( $log_context ) ) {
					$this->assign_terms( $log_post_id, $log_context, static::TAXONOMY_LOG_CONTEXT );
				}
			}

			// Create a unique transient key based on the log key and context.
			if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || apply_filters( 'ai_logger_use_log_lock', false ) ) {
				\set_transient( $transient_key, true, $this->throttle_limit );
			}

			// Remove the log from the stack.
			unset( $this->queue[ $transient_key ] );
		}
	}

	/**
	 * Process the queue when shutting down.
	 *
	 * Ensure that all logs are properly saved when shutting down (if any are left).
	 */
	public function process_queue_shutdown() {
		if ( empty( $this->queue ) ) {
			return;
		}

		$switching = \get_current_blog_id() !== $this->original_site_id;
		if ( $switching ) {
			\switch_to_blog( $this->original_site_id ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.switch_to_blog_switch_to_blog
		}

		$this->process_queue();

		if ( $switching ) {
			\restore_current_blog();
		}
	}

	/**
	 * Determines if this message should actually be inserted
	 * into the database. Will filter based on whether WP_DEBUG
	 * is defined as true (for info levels) and will throttle
	 * the overall inserts happening to the DB
	 *
	 * @param string $transient_key Transient key to store to.
	 * @param array  $log Log arguments.
	 * @access protected
	 * @return bool
	 */
	protected function insert_permitted( string $transient_key, array $log ): bool {
		// If the site is in debug mode, always write to the log.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		/**
		 * In production, do not write info messages to the log unless the
		 * filter has been overridden.
		 */
		if ( in_array( $log['level_name'], [ 'info', 'debug' ], true ) && ! \apply_filters( 'ai_logger_allow_production_info_logs', false ) ) {
			return false;
		}

		/**
		 * The throttling transient has expired if get_transient returns false,
		 * and a new insert should be permitted.
		 */
		return false === \get_transient( $transient_key );
	}

	/**
	 * Assign the terms associated with the new post, currently
	 * used to apply a Log Level (info, warning, error) and the
	 * custom context to a log
	 *
	 * @param int    $new_post_id Post ID.
	 * @param string $term Term name.
	 * @param string $taxonomy Taxonomy name.
	 * @access protected
	 * @return void
	 */
	protected function assign_terms( $new_post_id, $term, $taxonomy ) {
		$term_id       = false;
		$existing_term = \get_term_by( 'name', $term, $taxonomy );

		if ( ! $existing_term ) {
			$existing_term = \wp_insert_term( $term, $taxonomy );

			if ( ! empty( $existing_term ) && ! \is_wp_error( $existing_term ) ) {
				$term_id = $existing_term['term_id'];
			}
		} else {
			$term_id = $existing_term->term_id;
		}

		if ( $term_id ) {
			\wp_set_object_terms( $new_post_id, $term_id, $taxonomy );
		}
	}

	/**
	 * Check if the log should be written on shutdown.
	 *
	 * @return bool
	 */
	protected function should_write_on_shutdown(): bool {
		return (bool) \apply_filters( 'ai_logger_should_write_on_shutdown', true );
	}
}
