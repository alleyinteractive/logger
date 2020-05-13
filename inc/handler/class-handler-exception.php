<?php
/**
 * Handler_Exception class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

/**
 * Handler Exception
 */
class Handler_Exception extends \Exception {
	/**
	 * Exception context.
	 *
	 * @var array
	 */
	protected $context;

	/**
	 * Constructor.
	 *
	 * @param string $message Exception message.
	 * @param array  $context Exception context.
	 */
	public function __construct( string $message = '', array $context = [] ) {
		parent::__construct( $message );
		$this->context = $context;
	}

	/**
	 * Getter for exception context.
	 *
	 * @return array
	 */
	public function get_context(): array {
		return $this->context;
	}
}

