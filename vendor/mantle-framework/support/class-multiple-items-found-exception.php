<?php
/**
 * Multiple_Items_Found_Exception class file
 *
 * @package Mantle
 */

namespace Mantle\Support;

use RuntimeException;

/**
 * Exception thrown when multiple items are found where only one was expected.
 */
class Multiple_Items_Found_Exception extends RuntimeException {
	/**
	 * Create a new exception instance.
	 *
	 * @param  int             $count
	 * @param  int             $code
	 * @param  \Throwable|null $previous
	 */
	public function __construct( public int $count, int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( "{$count} items were found.", $code, $previous );
	}

	/**
	 * Get the number of items found.
	 */
	public function get_count(): int {
		return $this->count;
	}
}
