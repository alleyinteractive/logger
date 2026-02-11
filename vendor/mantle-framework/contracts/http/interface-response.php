<?php
/**
 * Response interface file
 *
 * @package Mantle
 */

declare(strict_types=1);

namespace Mantle\Contracts\Http;

/**
 * Response Interface
 *
 * Consistent response interface for HTTP responses used across packages.
 */
interface Response {
	/**
	 * Retrieve the response status code.
	 */
	public function get_status_code(): int;

	/**
	 * Retrieve all response headers.
	 *
	 * @return array<string, string> Array of headers.
	 */
	public function get_headers(): array;

	/**
	 * Retrieve a specific response header.
	 *
	 * @param string $name The header name to retrieve.
	 * @param bool   $as_array Whether to return the header as an array.
	 * @return ($as_array is true ? array<string> : string|null) The header value(s), or null if not found.
	 */
	public function get_header( string $name, bool $as_array = false ): string|array|null;

	/**
	 * Retrieve the response body.
	 *
	 * @return string|null The response body.
	 */
	public function get_body(): ?string;
}
