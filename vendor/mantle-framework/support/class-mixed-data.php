<?php
/**
 * Mixed_Data class file
 *
 * @package mantle-framework
 */

namespace Mantle\Support;

use ArrayAccess;
use Mantle\Contracts\Support\Jsonable;

/**
 * Fluent class for manipulating mixed data as type-safe objects.
 */
class Mixed_Data implements ArrayAccess, Jsonable, \JsonSerializable, \Stringable {
	use Interacts_With_Data;

	/**
	 * Create a new instance of the class from mixed data.
	 *
	 * @param mixed $data Mixed data.
	 */
	public static function of( mixed $data ): static {
		return new static( $data );
	}

	/**
	 * Create a new instance of the class.
	 *
	 * @param mixed $value Value.
	 */
	public static function create( mixed $value ): static {
		return new static( $value );
	}

	/**
	 * Constructor
	 *
	 * @param mixed $value Value.
	 * @param bool  $throw Optional. Whether to throw an exception if the value is not a compatible type.
	 */
	public function __construct( mixed $value, bool $throw = false ) {
		$this->throw = $throw;
		$this->value = $value;
	}
}
