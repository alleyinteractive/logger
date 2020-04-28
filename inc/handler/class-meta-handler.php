<?php
/**
 * Meta_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

/**
 * Meta Logging Handler
 *
 * Stores the log directly to a object's meta. Objects that can be logged
 * to are posts or terms. To support the switching of the current blog
 * via `switch_to_blog()`, the logger supports a queue to store
 * the log messages for once it is back to the original site. Logs will
 * be stored on 'shutdown'.
 */
abstract class Meta_Handler extends Handler {
	/**
	 * Object ID to store in.
	 *
	 * @var string
	 */
	protected $object_id;

	/**
	 * Meta key to store in.
	 *
	 * @var string
	 */
	protected $log_key;

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
	 * Constructor.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $log_key Meta key.
	 */
	public function __construct( int $object_id, string $log_key ) {
		$this->object_id        = $object_id;
		$this->log_key          = $log_key;
		$this->original_site_id = \get_current_blog_id();

		\add_action( 'shutdown', [ $this, 'process_queue_shutdown' ] );
	}

	/**
	 * Get the meta type for the logger.
	 *
	 * @return string
	 */
	abstract public function get_meta_type(): string;

	/**
	 * Clear the stored log.
	 */
	public function clear() {
		\delete_metadata( $this->get_meta_type(), $this->object_id, $this->log_key );
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
		$log_entry   = func_get_args();
		$log_entry[] = time();

		if ( get_current_blog_id() !== $this->original_site_id ) {
			$this->queue[] = $log_entry;
		} else {
			\add_metadata(
				$this->get_meta_type(),
				$this->object_id,
				$this->log_key,
				$log_entry,
				false
			);
		}
	}

	/**
	 * Process the queue of log messages.
	 *
	 * @param int $new_site_id New site ID.
	 */
	public function process_queue( $new_site_id ) {
		if ( $new_site_id !== $this->original_site_id ) {
			return;
		}

		// Process the queue and flush it.
		foreach ( $this->queue as $queue_item ) {
			\add_metadata(
				$this->get_meta_type(),
				$this->object_id,
				$this->log_key,
				$queue_item,
				false
			);
		}

		$this->queue = [];
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

		$this->process_queue( $this->original_site_id );

		if ( $switching ) {
			\restore_current_blog();
		}
	}

	/**
	 * Rotates the stored log.
	 *
	 * Deletes all log entires after a certain day cutoff.
	 *
	 * @param int $days The number of days to keep.
	 * @return Meta_Handler
	 */
	public function rotate( int $days = 7 ): Meta_Handler {
		$logs   = \get_post_meta( $this->object_id, $this->log_key, false );
		$cutoff = time() - $days * DAY_IN_SECONDS;
		foreach ( $logs as $log ) {
			if ( $cutoff > $log[3] ) {
				\delete_metadata( $this->get_meta_type(), $this->object_id, $this->log_key, $log );
			}
		}

		return $this;
	}
}
