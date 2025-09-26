<?php
/**
 * Router interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Http\Routing;

use Mantle\Http\Request;
use Mantle\Http\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;

/**
 * Router Contract
 */
interface Router {
	/**
	 * Register a route.
	 *
	 * @param string[]     $methods Methods to register.
	 * @param string       $uri URL route.
	 * @param array<mixed> $arguments Route callback.
	 */
	public function add_route( array $methods, string $uri, array $arguments ): Route;

	/**
	 * Register a REST API route.
	 *
	 * @param string[]     $methods Methods to register.
	 * @param string       $uri URL route.
	 * @param array<mixed> $arguments Route arguments.
	 */
	public function add_rest_route( array $methods, string $uri, array $arguments ): Route;

	/**
	 * Register a GET route.
	 *
	 * @param string $uri URL to register for.
	 * @param mixed  $action Callback action.
	 */
	public function get( string $uri, mixed $action = '' );

	/**
	 * Register a POST route.
	 *
	 * @param string $uri URL to register for.
	 * @param mixed  $action Callback action.
	 */
	public function post( string $uri, mixed $action = '' );

	/**
	 * Register a PUT route.
	 *
	 * @param string $uri URL to register for.
	 * @param mixed  $action Callback action.
	 */
	public function put( string $uri, mixed $action = '' );

	/**
	 * Register a DELETE route.
	 *
	 * @param string $uri URL to register for.
	 * @param mixed  $action Callback action.
	 */
	public function delete( string $uri, mixed $action = '' );

	/**
	 * Register a PATCH route.
	 *
	 * @param string $uri URL to register for.
	 * @param mixed  $action Callback action.
	 */
	public function patch( string $uri, mixed $action = '' );

	/**
	 * Register a OPTIONS route.
	 *
	 * @param string $uri URL to register for.
	 * @param mixed  $action Callback action.
	 */
	public function options( string $uri, mixed $action = '' );

	/**
	 * Register a route for any HTTP method.
	 *
	 * @param string $uri URL to register for.
	 * @param mixed  $action Callback action.
	 */
	public function any( string $uri, mixed $action = '' );

	/**
	 * Dispatch a request to the registered routes.
	 *
	 * @param Request $request Request object.
	 */
	public function dispatch( Request $request ): ?Response;

	/**
	 * Get registered routes.
	 */
	public function get_routes(): RouteCollection;

	/**
	 * Substitute Explicit Bindings
	 *
	 * @param Request $request Request object.
	 */
	public function substitute_bindings( Request $request );

	/**
	 * Substitute the implicit Eloquent model bindings for the route.
	 *
	 * @param Request $request Request instance.
	 */
	public function substitute_implicit_bindings( Request $request );

	/**
	 * Register a REST API route
	 *
	 * @param string                       $namespace        Namespace for the REST API route.
	 * @param callable|string              $callback_or_uri  Callback that will be invoked to register
	 *                                                       routes or a string route path.
	 * @param callable|array<mixed>|string $args             Callback for the route if $callback or route arguments.
	 */
	public function rest_api( string $namespace, callable|string $callback_or_uri, callable|array|string $args = [] ): ?Route;

	/**
	 * Rename a route.
	 *
	 * @param string $old_name Old route name.
	 * @param string $new_name New route name.
	 *
	 * @throws \InvalidArgumentException Thrown when attempting to rename a route
	 *                                  a name that is already taken.
	 */
	public function rename_route( string $old_name, string $new_name ): static;

	/**
	 * Register a group of middleware.
	 *
	 * @param  string $name
	 * @param  array  $middleware
	 */
	public function middleware_group( string $name, array $middleware ): static;

	/**
	 * Register a short-hand name for a middleware.
	 *
	 * @param  string $name
	 * @param  string $class
	 */
	public function alias_middleware( string $name, string $class ): static;

	/**
	 * Determine if the request should pass through to WordPress.
	 *
	 * @param (callable(Request): bool)|bool $callback Callback to determine if the request should pass through to WordPress.
	 */
	public function pass_requests_to_wordpress( callable|bool $callback ): static;

	/**
	 * Determine if the request should pass through to WordPress.
	 *
	 * @param Request $request Request object.
	 */
	public function should_pass_through_request( Request $request ): bool;

	/**
	 * Register the registered REST API routes with WordPress.
	 *
	 * Called on 'rest_api_init'.
	 */
	public function register_rest_routes(): void;
}
