<?php
/**
 * Interacts_With_Data trait file
 *
 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
 *
 * @package mantle-framework
 */

namespace Mantle\Support;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeZone;
use InvalidArgumentException;
use Mantle\Support\Traits\Conditionable;
use Mantle\Support\Traits\Macroable;
use Mantle\Support\Traits\Tappable;

use function Mantle\Support\Helpers\data_get;
use function Mantle\Support\Helpers\data_set;
use function Mantle\Support\Helpers\value;

/**
 * Fluent class for retrieving data as type-safe objects.
 */
trait Interacts_With_Data {
	use Conditionable;
	use Macroable;
	use Tappable;

	/**
	 * Whether to throw an exception if the value is not a compatible type.
	 */
	protected bool $throw = false;

	/**
	 * Value.
	 */
	protected mixed $value;

	/**
	 * Create a new instance of the class.
	 *
	 * @param mixed $value Value.
	 */
	abstract public static function create( mixed $value ): static;

	/**
	 * Retrieve the value as a string.
	 *
	 * @throws InvalidArgumentException If the value is not scalar and $throw is true.
	 */
	public function string(): string {
		if ( ! is_scalar( $this->value ) && $this->throw ) {
			throw new InvalidArgumentException( 'Value is not scalar and cannot be cast to a string.' );
		}

		return (string) $this->value;
	}

	/**
	 * Retrieve the value as a Stringable object.
	 */
	public function stringable(): Stringable {
		return new Stringable( $this->string() );
	}

	/**
	 * Retrieve the value as an integer.
	 *
	 * @throws InvalidArgumentException If the value is not numeric and $throw is true.
	 */
	public function int(): int {
		if ( ! is_numeric( $this->value ) && $this->throw ) {
			throw new InvalidArgumentException( 'Value is not numeric and cannot be cast to an integer.' );
		}

		return (int) $this->value;
	}

	/**
	 * Alias for int().
	 */
	public function integer(): int {
		return $this->int();
	}

	/**
	 * Retrieve the value as a float.
	 *
	 * @throws InvalidArgumentException If the value is not numeric and $throw is true.
	 */
	public function float(): float {
		if ( ! is_numeric( $this->value ) && $this->throw ) {
			throw new InvalidArgumentException( 'Value is not numeric and cannot be cast to a float.' );
		}

		return (float) $this->value;
	}

	/**
	 * Retrieve the value as a boolean.
	 */
	public function bool(): bool {
		if ( is_bool( $this->value ) ) {
			return $this->value;
		}

		if ( function_exists( 'wp_validate_boolean' ) ) {
			return wp_validate_boolean( $this->value );
		}

		if ( is_string( $this->value ) && 'false' === strtolower( $this->value ) ) {
			return false;
		}

		return (bool) $this->value;
	}

	/**
	 * Check if the value is truthy.
	 *
	 * "truthy" means that the value will evaluate to be "true" when used in an if
	 * statement. For example, 0 or '0' will evaluate to false, while '1', 'true',
	 * or 'a random string' will evaluate to true.
	 */
	public function truthy(): bool {
		return (bool) $this->value;
	}

	/**
	 * Alias for bool().
	 */
	public function boolean(): bool {
		return $this->bool();
	}

	/**
	 * Retrieve the value as an array.
	 *
	 * @return array<mixed>
	 */
	public function array(): array {
		return (array) $this->value;
	}

	/**
	 * Retrieve the value as a collection.
	 */
	public function collection(): Collection {
		return new Collection( $this->value );
	}

	/**
	 * Alias for collection().
	 */
	public function collect(): Collection {
		return $this->collection();
	}

	/**
	 * Retrieve the value as a Carbon instance.
	 *
	 * @param string|null                  $format Date format.
	 * @param DateTimeZone|string|int|null $timezone Timezone.
	 */
	public function date( ?string $format = null, DateTimeZone|string|int|null $timezone = null ): ?Carbon {
		if ( $this->is_empty() ) {
			return null;
		}

		if ( $format ) {
			return Carbon::createFromFormat( $format, $this->string(), $timezone );
		}

		try {
			return Carbon::parse( $this->string(), $timezone );
		} catch ( InvalidFormatException ) {
			return null;
		}
	}

	/**
	 * Retrieve the value as an object.
	 */
	public function object(): object {
		return (object) $this->value;
	}

	/**
	 * Check if the value is empty.
	 */
	public function is_empty(): bool {
		return empty( $this->value );
	}

	/**
	 * Check if the value is not empty.
	 */
	public function is_not_empty(): bool {
		return ! $this->is_empty();
	}

	/**
	 * Check if the value is null.
	 */
	public function is_null(): bool {
		return null === $this->value;
	}

	/**
	 * Check if the value is not null.
	 */
	public function is_not_null(): bool {
		return ! $this->is_null();
	}

	/**
	 * Check if the value is false.
	 */
	public function is_false(): bool {
		return false === $this->value;
	}

	/**
	 * Check if the value is true.
	 */
	public function is_true(): bool {
		return true === $this->value;
	}

	/**
	 * Check if the value is a specific type.
	 *
	 * @param string $type Type to check.
	 */
	public function is_type( string $type ): bool {
		return gettype( $this->value ) === $type;
	}

	/**
	 * Check if the value is not a specific type.
	 *
	 * @param string $type Type to check.
	 */
	public function is_not_type( string $type ): bool {
		return ! $this->is_type( $type );
	}

	/**
	 * Check if the value is an array.
	 */
	public function is_array(): bool {
		return is_array( $this->value );
	}

	/**
	 * Check if the value is not an array.
	 */
	public function is_not_array(): bool {
		return ! $this->is_array();
	}

	/**
	 * Check if the value is an object.
	 */
	public function is_object(): bool {
		return is_object( $this->value );
	}

	/**
	 * Check if the value is not an object.
	 */
	public function is_not_object(): bool {
		return ! $this->is_object();
	}

	/**
	 * Retrieve the raw value of the option.
	 */
	public function value(): mixed {
		return $this->value;
	}

	/**
	 * Set the option value.
	 *
	 * @param mixed $value Option value.
	 */
	public function set( mixed $value ): void {
		$this->value = $value;
	}

	/**
	 * Dump the value.
	 */
	public function dump(): static {
		dump( $this->value );

		return $this;
	}

	/**
	 * Dump the value and exit.
	 */
	public function dd(): never {
		dd( $this->value );
	}

	/**
	 * Set whether to throw an exception if the value is not a compatible type.
	 *
	 * @param bool $throw Whether to throw an exception.
	 */
	public function throw( bool $throw = true ): static {
		$this->throw = $throw;

		return $this;
	}

	/**
	 * Set whether to throw an exception if the condition is met.
	 *
	 * @param (callable(): bool)|bool $condition Condition to check.
	 */
	public function throw_if( callable|bool $condition ): static {
		$this->throw = (bool) value( $condition );

		return $this;
	}

	/**
	 * Retrieve a sub-property from an array value.
	 *
	 * @throws InvalidArgumentException If the value is not an array and $throw is true.
	 *
	 * @param string $property Property name. Supports dot notation.
	 * @param mixed  $default Default value. Default is null.
	 */
	public function get( string $property, mixed $default = null ): static {
		if ( ! $this->is_array() && ! $this->is_object() && $this->throw ) {
			throw new InvalidArgumentException( 'Value is not an array or object and cannot retrieve a sub-property.' );
		}

		return static::create( data_get( $this->value, $property, $default ) );
	}

	/**
	 * Check if a property or a set of properties exists in the value.
	 *
	 * @param string ...$property Property name. Supports dot notation.
	 */
	public function has( string ...$property ): bool {
		foreach ( $property as $prop ) {
			if ( $this->get( $prop )->is_empty() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if a property or a set of properties is missing in the value.
	 *
	 * @param string ...$property Property name. Supports dot notation.
	 */
	public function missing( string ...$property ): bool {
		return ! $this->has( ...$property );
	}

	/**
	 * Check if any of the properties exist in the value.
	 *
	 * @param string ...$property Property name. Supports dot notation.
	 */
	public function has_any( string ...$property ): bool {
		foreach ( $property as $prop ) {
			if ( ! $this->get( $prop )->is_empty() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Convert the value to its JSON representation.
	 *
	 * @param int $options json_encode() options.
	 */
	public function to_json( $options = 0 ): string {
		return json_encode( $this->value, $options ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}

	/**
	 * Convert the value to its JSON representation.
	 */
	public function jsonSerialize(): mixed {
		return $this->value;
	}

	/**
	 * Convert the value to its string representation.
	 */
	public function __toString(): string {
		return $this->string();
	}

	/**
	 * Check if a property exists in a value.
	 *
	 * @param mixed $offset
	 */
	public function offsetExists( mixed $offset ): bool {
		return '__not_found__' !== $this->get( $offset, '__not_found__' )->value;
	}

	/**
	 * Retrieve an offset from the value.
	 *
	 * @param mixed $offset Offset name.
	 */
	public function offsetGet( mixed $offset ): mixed {
		return data_get( $this->value, $offset );
	}

	/**
	 * Set an offset in the value.
	 *
	 * @param mixed $offset Offset name.
	 * @param mixed $value value.
	 */
	public function offsetSet( mixed $offset, mixed $value ): void {
		$data = $this->value;

		data_set( $data, $offset, $value );

		$this->set( $data );
	}

	/**
	 * Unset an offset in the value.
	 *
	 * @throws InvalidArgumentException If the value is immutable.
	 *
	 * @param mixed $offset Offset name.
	 */
	public function offsetUnset( mixed $offset ): void {
		$data = $this->value;

		unset( $data[ $offset ] );

		$this->set( $data );
	}
}
