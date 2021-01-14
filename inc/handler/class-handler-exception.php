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
	 * Exception record.
	 *
	 * @var array
	 */
	protected $record;

	/**
	 * Constructor.
	 *
	 * @param string $message Exception message.
	 * @param array  $context Exception context.
	 * @param array  $record Log record.
	 */
	public function __construct( string $message = '', array $context = [], array $record = [] ) {
		parent::__construct( $message );

		$this->context = $context;
		$this->record  = $record;
	}

	/**
	 * Getter for exception context.
	 *
	 * @return array
	 */
	public function get_context(): array {
		return $this->context;
	}

	/**
	 * Getter for the exception record.
	 *
	 * @return array
	 */
	public function get_record(): array {
		return $this->record;
	}
}
