<?php
/**
 * Logger class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

use AI_Logger\Handler\Handler_Interface;
use Psr\Log\{
	AbstractLogger,
	LoggerInterface,
};

/**
 * Logger Channel
 *
 * Provides a way to pass logs to multiple handlers. Mirrors the Monolog Logger
 * class to provide an extendable interface for logging.
 *
 * @link https://github.com/Seldaek/monolog/blob/master/src/Monolog/Logger.php
 */
class Logger extends AbstractLogger implements LoggerInterface {
	/**
	 * The handler stack
	 *
	 * @var Handler_Interface[]
	 */
	protected $handlers;

	/**
	 * Constructor.
	 *
	 * @param string              $name Channel name.
	 * @param Handler_Interface[] $handlers Stack of handlers.
	 */
	public function __construct( string $name, array $handlers = [] ) {
		$this->name = $name;

		if ( ! empty( $handlers ) ) {
			array_map( [ $this, 'add_handler' ], $handlers );
		}
	}

	/**
	 * Getter for the channel name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Add a handler to the logger.
	 *
	 * @param Handler_Interface $handler Handler to add.
	 */
	public function add_handler( Handler_Interface $handler ): self {
		$this->handlers[] = $handler;
		return $this;
	}

	/**
	 * Getter for handlers.
	 *
	 * @return Handler_Interface[]
	 */
	public function get_handlers(): array {
		return $this->handlers;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param string  $level Log level.
	 * @param string  $message Log message.
	 * @param mixed[] $context Log context.
	 *
	 * @return void
	 *
	 * @throws Invalid_Handlers_Exception Thrown for missing handlers.
	 */
	public function log( $level, $message, array $context = [] ) {
		if ( empty( $this->handlers ) ) {
			throw new Invalid_Handlers_Exception( __( 'No log handlers set.', 'ai-logger' ) );
		}

		foreach ( $this->handlers as $handler ) {
			$handler->handle( $level, $message, $context );
		}
	}
}
