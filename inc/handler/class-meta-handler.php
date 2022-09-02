<?php
/**
 * Meta_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Meta Logging Handler
 *
 * Stores the log directly to a object's meta. Objects that can be logged
 * to are posts or terms. To support the switching of the current blog
 * via `switch_to_blog()`, the logger supports a queue to store
 * the log messages for once it is back to the original site. Logs will
 * be stored on 'shutdown'.
 */
abstract class Meta_Handler extends AbstractProcessingHandler implements Handler_Interface {
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
	 * @param int        $object_id Object ID to use.
	 * @param string     $log_key Meta key to use, defaults to 'log'.
	 * @param int|string $level  The minimum logging level at which this handler will be triggered.
	 * @param bool       $bubble Whether the messages that are handled can bubble up the stack or not.
	 */
	public function __construct( int $object_id, string $log_key = 'log', string $level = Logger::DEBUG, bool $bubble = true ) {
		parent::__construct( $level, $bubble );

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
	 * @param array $record Log record.
	 */
	protected function write( array $record ): void {
		if ( \get_current_blog_id() !== $this->original_site_id ) {
			$this->queue[] = $record;
		} else {
			$this->write_log_record( $record );
		}
	}

	/**
	 * Writer the log record to the object meta.
	 *
	 * @param array $record Log Record.
	 */
	protected function write_log_record( array $record ) {
		\add_metadata(
			$this->get_meta_type(),
			$this->object_id,
			$this->log_key,
			$record,
			false
		);
	}

	/**
	 * Process the queue of log messages.
	 */
	public function process_queue() {
		if ( \get_current_blog_id() !== $this->original_site_id ) {
			return;
		}

		// Process the queue and flush it.
		foreach ( $this->queue as $queue_item ) {
			$this->write_log_record( $queue_item );
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
		$logs   = \get_metadata( $this->get_meta_type(), $this->object_id, $this->log_key, false );
		$cutoff = time() - $days * DAY_IN_SECONDS;
		foreach ( $logs as $log ) {
			if ( $cutoff > $log[3] ) {
				\delete_metadata( $this->get_meta_type(), $this->object_id, $this->log_key, $log );
			}
		}

		return $this;
	}
}
