<?php
/**
 * Option class file
 *
 * @package mantle-framework
 */

namespace Mantle\Support;

use ArrayAccess;
use InvalidArgumentException;
use Mantle\Contracts\Support\Jsonable;

/**
 * Fluent class for retrieving options as type-safe objects.
 *
 * When retrieving options from the database, get_option() has a return value of
 * mixed. This class allows you to retrieve options with a specific type.
 */
class Option implements ArrayAccess, Jsonable, \JsonSerializable, \Stringable {
	use Interacts_With_Data;

	/**
	 * Retrieve an option from the database.
	 *
	 * @param string $option Option name.
	 * @param mixed  $default Default value. Default is null.
	 */
	public static function of( string $option, mixed $default = null ): static {
		return new static( $option, get_option( $option, $default ) );
	}

	/**
	 * Create a new instance of the class.
	 *
	 * @param mixed $value Value.
	 */
	public static function create( mixed $value ): static {
		return new static( null, $value );
	}

	/**
	 * Constructor
	 *
	 * @param string|null $option Option name.
	 * @param mixed       $value Option value.
	 * @param bool        $throw Whether to throw an exception if the option is not a compatible type.
	 */
	public function __construct( protected readonly ?string $option, mixed $value, bool $throw = false ) {
		$this->value = $value;
		$this->throw = $throw;
	}

	/**
	 * Save the option.
	 *
	 * @throws InvalidArgumentException If the option is a sub-property of an option and the option name is not passed.
	 */
	public function save(): static {
		if ( ! $this->option ) {
			throw new InvalidArgumentException( 'Unable to save sub-property of an option.' );
		}

		update_option( $this->option, $this->value );

		$this->value = get_option( $this->option );

		return $this;
	}

	/**
	 * Delete the option.
	 *
	 * @throws InvalidArgumentException If the option is a sub-property of an option.
	 */
	public function delete(): void {
		if ( ! $this->option ) {
			throw new InvalidArgumentException( 'Unable to delete option on a sub-property of an option.' );
		}

		delete_option( $this->option );
	}
}
