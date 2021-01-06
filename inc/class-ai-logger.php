<?php
/**
 * AI_Logger class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Main class responsible for defining the logger functionality
 */
class AI_Logger implements LoggerInterface {
	/**
	 * Class instance.
	 *
	 * @var AI_Logger
	 */
	protected static $instance;

	/**
	 * Logger with a Post Handler attached.
	 *
	 * @var \Monolog\Logger|\Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Get the instance of this singleton
	 *
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
			$this->get_handlers(),
			$this->get_processors(),
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
	 * Retrieve default handlers for Monolog.
	 *
	 * @return array
	 */
	protected function get_handlers(): array {
		$handlers = [];

		if ( defined( 'WP_CLI' ) && WP_CLI && ! wp_doing_cron() ) {
			$handlers[] = new Handler\CLI_Handler();
		} else {
			$handlers[] = new Handler\Post_Handler();
		}

		/**
		 * Filter the default handlers for Monolog.
		 *
		 * @param \Monolog\Handler\HandlerInterface[] $handlers Monolog handlers.
		 */
		return (array) \apply_filters( 'ai_logger_handlers', $handlers );
	}

	/**
	 * Retrieve default processors for Monolog.
	 *
	 * @return array
	 */
	protected function get_processors(): array {
		/**
		 * Filter the default processors for Monolog.
		 *
		 * @param \Monolog\Processor\ProcessorInterface[] $processors Monolog processors.
		 */
		return (array) apply_filters( 'ai_logger_processors', [ new \Monolog\Processor\WebProcessor() ] );
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

	/**
	 * System is unusable.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function emergency( $message, array $context = [] ) {
		$this->logger->emergency( $message, $context );
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function alert( $message, array $context = [] ) {
		$this->logger->alert( $message, $context );
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function critical( $message, array $context = [] ) {
		$this->logger->critical( $message, $context );
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function error( $message, array $context = [] ) {
		$this->logger->error( $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function warning( $message, array $context = [] ) {
		$this->logger->warning( $message, $context );
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function notice( $message, array $context = [] ) {
		$this->logger->notice( $message, $context );
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 * @return void
	 */
	public function info( $message, array $context = [] ) {
		$this->logger->info( $message, $context );
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function debug( $message, array $context = [] ) {
		$this->logger->debug( $message, $context );
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed   $level Log level.
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = [] ) {
		$this->logger->log( $level, $message, $context );
	}
}
