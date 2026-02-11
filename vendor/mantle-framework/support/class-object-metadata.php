<?php
/**
 * Object_Metadata class file
 *
 * @package mantle-framework
 */

namespace Mantle\Support;

use ArrayAccess;
use InvalidArgumentException;
use Mantle\Contracts\Support\Jsonable;

/**
 * Fluent class for retrieving object meta data as type-safe objects.
 *
 * Supports all meta data objects in WordPress: posts, terms, users, and comments.
 */
class Object_Metadata implements ArrayAccess, Jsonable, \JsonSerializable, \Stringable {
	use Interacts_With_Data;

	/**
	 * Retrieve metadata from the database.
	 *
	 * @param string $meta_type Meta type.
	 * @param int    $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $default Default value. Default is null.
	 */
	public static function of( string $meta_type, int $object_id, string $meta_key, mixed $default = null ): static {
		$value = get_metadata( $meta_type, $object_id, $meta_key, true );

		if ( '' === $value ) {
			$value = $default;
		}

		return new static( $meta_type, $object_id, $meta_key, $value );
	}

	/**
	 * Create a new instance of the class.
	 *
	 * @param mixed $value Value.
	 */
	public static function create( mixed $value ): static {
		return new static( null, null, null, $value );
	}

	/**
	 * Constructor.
	 *
	 * @param string|null $meta_type Meta type.
	 * @param int|null    $object_id Object ID.
	 * @param string|null $meta_key Meta key.
	 * @param mixed       $value Meta value.
	 * @param bool        $throw Whether to throw an exception if the metadata is not a compatible type.
	 */
	public function __construct( protected readonly ?string $meta_type, protected readonly ?int $object_id, protected readonly ?string $meta_key, mixed $value, bool $throw = false ) {
		$this->throw = $throw;
		$this->value = $value;
	}

	/**
	 * Save the meta data.
	 *
	 * @throws InvalidArgumentException If the object is retrieving sub-property of a metadata and the meta type is not passed.
	 */
	public function save(): static {
		if ( ! $this->meta_type || ! $this->object_id || ! $this->meta_key ) {
			throw new InvalidArgumentException( 'Unable to save sub-property of a metadata.' );
		}

		update_metadata( $this->meta_type, $this->object_id, $this->meta_key, $this->value );

		$this->value = get_metadata( $this->meta_type, $this->object_id, $this->meta_key, true );

		return $this;
	}

	/**
	 * Add the meta data.
	 *
	 * @throws InvalidArgumentException If the object is retrieving sub-property of a metadata and the meta type is not passed.
	 */
	public function add(): static {
		if ( ! $this->meta_type || ! $this->object_id || ! $this->meta_key ) {
			throw new InvalidArgumentException( 'Unable to add sub-property of a metadata.' );
		}

		add_metadata( $this->meta_type, $this->object_id, $this->meta_key, $this->value );

		$this->value = get_metadata( $this->meta_type, $this->object_id, $this->meta_key, true );

		return $this;
	}

	/**
	 * Delete the meta data.
	 *
	 * @throws InvalidArgumentException If the object is a sub-property of a metadata.
	 */
	public function delete(): void {
		if ( ! $this->meta_type || ! $this->object_id || ! $this->meta_key ) {
			throw new InvalidArgumentException( 'Unable to delete meta on a sub-property of a metadata.' );
		}

		delete_metadata( $this->meta_type, $this->object_id, $this->meta_key );
	}

	/**
	 * Delete a specific meta key that matches the value.
	 *
	 * @param mixed $value Value to delete.
	 *
	 * @throws InvalidArgumentException If the object is a sub-property of a metadata.
	 */
	public function delete_value( mixed $value ): void {
		if ( ! $this->meta_type || ! $this->object_id || ! $this->meta_key ) {
			throw new InvalidArgumentException( 'Unable to delete meta on a sub-property of a metadata.' );
		}

		delete_metadata( $this->meta_type, $this->object_id, $this->meta_key, $value );
	}

	/**
	 * Fetch all meta values for the given meta.
	 *
	 * @throws InvalidArgumentException If the object is a sub-property of a metadata.
	 */
	public function all(): static {
		if ( ! $this->meta_type || ! $this->object_id || ! $this->meta_key ) {
			throw new InvalidArgumentException( 'Unable to fetch all meta on a sub-property of a metadata.' );
		}

		$value = get_metadata( $this->meta_type, $this->object_id, $this->meta_key );

		return new static( $this->meta_type, $this->object_id, $this->meta_key, $value, $this->throw );
	}
}
