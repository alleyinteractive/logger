<?php
/**
 * Route_Registrar interface file
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Http\Routing;

use Mantle\Http\Routing\Route;

/**
 * Route Registrar Contract
 */
interface Route_Registrar {
	/**
	 * Set the value for a given attribute.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 */
	public function attribute( string $key, mixed $value ): static;

	/**
	 * Retrieve the registrar's attributes.
	 *
	 * @return array<mixed>
	 */
	public function attributes(): array;

	/**
	 * Register a route.
	 *
	 * @param string|string[]                   $method HTTP methods.
	 * @param string                            $uri Route URI.
	 * @param \Closure|array<mixed>|string|null $action Route action.
	 */
	public function register_route( string|array $method, string $uri, \Closure|array|string|null $action = null ): Route;
}
