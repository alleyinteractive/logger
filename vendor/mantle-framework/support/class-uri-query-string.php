<?php
/**
 * Uri_Query_String class file
 *
 * @package mantle
 */

namespace Mantle\Support;

use Mantle\Contracts\Support\Arrayable;
use League\Uri\QueryString;
use Stringable;

use function Mantle\Support\Helpers\data_get;

/**
 * URI Query String
 *
 * Manage the query string of Uri.
 */
class Uri_Query_String implements Arrayable, Stringable {

	/**
	 * Parsed query string.
	 *
	 * @var array<string, mixed>
	 */
	protected array $parsed_query;

	/**
	 * Create a new URI query string instance.
	 *
	 * @param Uri $uri The URI instance containing the query string.
	 */
	public function __construct( protected Uri $uri ) {
		$this->parsed_query = QueryString::extract( $this->uri->get_uri()->getQuery() );
	}

	/**
	 * Retrieve a value from the query string.
	 *
	 * @param string $property Property name to retrieve.
	 * @param mixed  $default  Default value to return if the property does not exist.
	 */
	public function get( string $property, mixed $default = null ): mixed {
		return data_get( $this->parsed_query, $property, $default );
	}

	/**
	 * Retrieved a value from the query string as Mixed_Data object.
	 *
	 * @param string $property Property name to retrieve.
	 * @param mixed  $default   Default value to return if the property does not exist.
	 */
	public function mixed( string $property, mixed $default = null ): Mixed_Data {
		return Mixed_Data::of( $this->get( $property, $default ) );
	}

	/**
	 * Retrieve all values from the query string as an array.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		return $this->parsed_query;
	}

	/**
	 * Convert the query string to an array.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return $this->parsed_query;
	}

	/**
	 * Check if a property exists in the query string.
	 *
	 * @param string $property Property name to check for existence.
	 */
	public function has( string $property ): bool {
		return array_key_exists( $property, $this->parsed_query );
	}

	/**
	 * Check if the query string is missing a property.
	 *
	 * @param string $property Property name to check for absence.
	 */
	public function missing( string $property ): bool {
		return ! $this->has( $property );
	}

	/**
	 * Retrieve the query string value.
	 */
	public function value(): string {
		return (string) $this;
	}

	/**
	 * Get the URL decoded version of the query string.
	 */
	public function decode(): string {
		return rawurldecode( (string) $this );
	}

	/**
	 * Get the string representation of the query string.
	 */
	public function __toString(): string {
		return (string) $this->uri->get_uri()->getQuery();
	}
}
