<?php
/**
 * Post_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use AI_Logger\AI_Logger;
use AI_Logger\Backtrace\Frame;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame as SpatieFrame;
use Throwable;

/**
 * Post Log Handler
 *
 * Writes logs to a custom post type that allows logs to be viewed
 * across the site.
 */
class Post_Handler extends AbstractProcessingHandler implements Handler_Interface {
	use Process_Shutdown;

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
		if ( empty( $record['context'] ) || 'front-end' !== $record['context'] ) {
			// Store the backtrace for only this handler. Not created as a processor
			// to avoid bloat of backtrace on all log types.
			$record['extra']['backtrace'] = Backtrace::create()->startingFromFrame(
				fn ( SpatieFrame $frame ) => ! in_array(
					$frame->class,
					[
						static::class,
						AI_Logger::class,
						\Monolog\Handler\AbstractProcessingHandler::class,
						\Monolog\Logger::class,
					],
					true
				)
			)
				->frames();

			$record['extra']['backtrace'] = array_map(
				fn ( SpatieFrame $frame ) => Frame::from_base( $frame ),
				$record['extra']['backtrace'],
			);

			/**
			 * Filter the number of code frames to store in the log.
			 *
			 * @param int   $frames Number of code frames to store.
			 * @param array $record Log record.
			 */
			$frames = min( (int) apply_filters( 'ai_logger_backtrace_code_frames', 8, $record ), count( $record['extra']['backtrace'] ) );

			if ( $frames > 0 ) {
				/**
				 * Filter the number of lines to store for each code frame.
				 *
				 * @param int   $frame_lines Number of lines to store for each code frame.
				 * @param array $record Log record.
				 */
				$frame_lines = (int) apply_filters( 'ai_logger_backtrace_code_lines', 5, $record );

				for ( $i = 0; $i < $frames; $i++ ) {
					// Enforce a maximum of 20 lines per frame with a default of 5.
					$record['extra']['backtrace'][ $i ]->load_snippet( max( $frame_lines, 20 ) );
				}
			}
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
				// Sanitize the context to prevent an accidental serialize error. When serializing
				// an exception, PHP will throw a Serialization of 'Closure' is not allowed error.
				if ( ! empty( $log['context'] ) && is_array( $log['context'] ) ) {
					$log['context'] = array_map(
						function ( mixed $value ) {
							if ( $value instanceof Throwable ) {
								if ( method_exists( $value, '__toString' ) ) {
									return $value->__toString();
								} elseif ( method_exists( $value, 'getTraceAsString' ) ) {
									return $value->getTraceAsString();
								}

								return $value->getMessage();
							}

							return $value;
						},
						$log['context'],
					);
				}

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
	 *
	 * @throws Throwable If an error occurs while processing the queue during testing.
	 */
	public function process_queue_shutdown() {
		if ( empty( $this->queue ) ) {
			return;
		}

		$switching = \get_current_blog_id() !== $this->original_site_id;
		if ( $switching ) {
			\switch_to_blog( $this->original_site_id ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.switch_to_blog_switch_to_blog
		}

		try {
			$this->process_queue();
		} catch ( Throwable $e ) {
			// Throw the exception if testing.
			if ( defined( 'MANTLE_IS_TESTING' ) && MANTLE_IS_TESTING ) {
				throw $e;
			}

			// In the event of any error, log to the actual error log if an exception
			// is thrown to prevent an exception from bubbling up.
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				"AI Logger: Error processing queue during shutdown: {$e->getMessage()}",
				E_ERROR,
			);
		}

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
		 * Allow unrestricted logging if filtered.
		 *
		 * @param bool  $unrestricted_logging Whether to allow unrestricted logging.
		 * @param array $log Log arguments.
		 */
		if ( \apply_filters( 'ai_logger_unrestricted_logging', false, $log ) ) {
			return true;
		}

		/**
		 * The throttling transient has expired if get_transient returns false,
		 * and a new insert should be permitted.
		 */
		return false === \get_transient( $transient_key );
	}

	/**
	 * Assign the terms associated with the new post, currently used to apply a
	 * Log Level (info, warning, error) and the custom context to a log
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
}
