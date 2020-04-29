<?php
/**
 * AI_Logger class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

/**
 * Main class responsible for defining the logger functionality
 */
class AI_Logger {

	/**
	 * Class instance.
	 *
	 * @var AI_Logger
	 * @access protected
	 * @static
	 */
	protected static $instance;

	/**
	 * Logger with a Post Handler attached.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Get the instance of this singleton
	 *
	 * @access public
	 * @static
	 * @return AI_Logger
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Register various actions & filters, initialize the object.
	 */
	protected function __construct() {
		new Data_Structures();

		// Setup the global post logger.
		$this->logger = new Logger(
			__( 'Post Logger', 'ai-logger' ),
			[ new Handler\Post_Handler() ]
		);

		\add_action( 'ai_logger_insert', [ $this, 'insert_legacy_log' ], 10, 3 );
		\add_action( 'init', [ AI_Logger_Garbage_Collector::class, 'add_hooks' ] );
	}

	/**
	 * Getter for the Logger instance.
	 *
	 * @return Logger
	 */
	public function get_logger(): Logger {
		return $this->logger;
	}

	/**
	 * Legacy log handler attached to the 'ai_logger_insert' event.
	 *
	 * @param string $key A short and unique title for the log entry.
	 * @param string $message An info or error message.
	 * @param array  $args Arguments (optional).
	 */
	public function insert_legacy_log( $key, $message, $args = [] ) {
		$this->logger->log(
			$args['level'] ?? 'error',
			$key,
			array_merge(
				[
					'content' => $message,
				],
				$args
			),
		);
	}

	/**
	 * Pass all unknown methods to the log handler.
	 *
	 * @param string $method Method called.
	 * @param array  $args Arguments for the method.
	 */
	public static function __callStatic( string $method, array $args = [] ) {
		return call_user_func_array(
			[ static::instance()->get_logger(), 'log' ],
			array_merge(
				[
					$method,
				],
				$args
			)
		);
	}
}
