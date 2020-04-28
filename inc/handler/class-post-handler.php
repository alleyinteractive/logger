<?php
/**
 * Post_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Psr\Log\LogLevel;

/**
 * Post Log Handler
 *
 * Writes logs to a custom post type that allows logs to be viewed
 * across the site.
 */
class Post_Handler extends Handler {
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
	 * A predefined list of log levels that are permitted.
	 * These are stored as terms in the Level taxonomy.
	 *
	 * @var array
	 * @access protected
	 */
	protected $allowed_levels = [];

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
	 */
	public function __construct() {
		/**
		 * Log levels according to RFC 5424.
		 *
		 * @link https://tools.ietf.org/html/rfc5424
		 */
		$this->allowed_levels = array(
			LogLevel::EMERGENCY => __( 'Emergency', 'ai-logger' ),
			LogLevel::ALERT     => __( 'Alert', 'ai-logger' ),
			LogLevel::CRITICAL  => __( 'Critical', 'ai-logger' ),
			LogLevel::ERROR     => __( 'Error', 'ai-logger' ),
			LogLevel::WARNING   => __( 'Warning', 'ai-logger' ),
			LogLevel::NOTICE    => __( 'Notice', 'ai-logger' ),
			LogLevel::INFO      => __( 'Info', 'ai-logger' ),
			LogLevel::DEBUG     => __( 'Debug', 'ai-logger' ),
			'log'               => __( 'Log', 'ai-logger' ),
		);

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
	 * @param string $level Log level {@see Psr\Log\LogLevel}.
	 * @param string $message Log message.
	 * @param array  $context Context to store.
	 */
	public function log( $level, $message, array $context = [] ) {
		// Ensure the log entry is always 3 items.
		$log_entry   = array_slice( func_get_args(), 0, 3 );
		$log_entry[] = time();

		$transient_key = 'ai_log_' . md5( \wp_json_encode( $log_entry ) );

		$this->queue[ $transient_key ] = $log_entry;

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
			// var_dump($log);exit;
			// Determine if this insert should actually write to the DB.
			if ( ! $this->insert_permitted( $transient_key, $log ) ) {
				continue;
			}

			list( $level, $title, $context ) = $log;

			// Log message content.
			$content     = $context['content'] ?? '';
			$log_context = $context['context'] ?? '';

			$log_post_id = \wp_insert_post(
				[
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_content'   => $content,
					'post_status'    => 'publish',
					'post_title'     => $title,
					'post_type'      => static::POST_TYPE,
				]
			);

			if ( ! empty( $log_post_id ) ) {
				if ( ! empty( $this->allowed_levels[ $level ] ) ) {
					$this->assign_terms( $log_post_id, $this->allowed_levels[ $level ], static::TAXONOMY_LOG_LEVEL );
				}

				if ( ! empty( $log_context ) ) {
					$this->assign_terms( $log_post_id, $log_context, static::TAXONOMY_LOG_CONTEXT );
				}
			}

			// Create a unique transient key based on the log key and context.
			if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
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
	protected function insert_permitted( $transient_key, $log ): bool {
		// If the site is in debug mode, always write to the log.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		/**
		 * In production, do not write info messages to the log unless the
		 * filter has been overridden.
		 */
		if ( 'info' === $log['args']['level'] && ! \apply_filters( 'ai_logger_allow_production_info_logs', false ) ) {
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
