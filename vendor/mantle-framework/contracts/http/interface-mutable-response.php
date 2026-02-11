<?php
/**
 * Mutable_Response interface file
 *
 * @package Mantle
 */

declare(strict_types=1);

namespace Mantle\Contracts\Http;

/**
 * Mutable Response Interface
 *
 * Consistent response interface for HTTP responses used across packages for a
 * mutable response.
 */
interface Mutable_Response extends Response {
	/**
	 * Set the response status code.
	 *
	 * @param int $code The status code to set.
	 */
	public function set_status_code( int $code ): void;

	/**
	 * Set the response headers.
	 *
	 * @param array<string, string> $headers The headers to set.
	 */
	public function set_headers( array $headers ): void;

	/**
	 * Set a response header.
	 *
	 * @param string          $name The header name.
	 * @param string|string[] $value The header value.
	 */
	public function set_header( string $name, string|array $value ): void;

	/**
	 * Delete a response header.
	 *
	 * @param string $name The header name to delete.
	 */
	public function delete_header( string $name ): void;

	/**
	 * Set the response body.
	 *
	 * @param ?string $body The response body to set.
	 */
	public function set_body( ?string $body ): void;
}
