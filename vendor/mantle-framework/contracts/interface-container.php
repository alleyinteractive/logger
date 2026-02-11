<?php
/**
 * Container Contract
 *
 * @package Mantle
 */

namespace Mantle\Contracts;

use ArrayAccess;
use Closure;
use Psr\Container\ContainerInterface;

/**
 * Container Contract
 */
interface Container extends ArrayAccess, ContainerInterface {
	/**
	 * Determine if the given abstract type has been bound.
	 *
	 * @param string $abstract Abstract name.
	 */
	public function bound( string $abstract ): bool;

	/**
	 * Alias a type to a different name.
	 *
	 * @param string $abstract Abstract name.
	 * @param string $alias Alias name.
	 */
	public function alias( string $abstract, string $alias ): void;

	/**
	 * Register a binding with the container.
	 *
	 * @param string               $abstract Abstract name.
	 * @param \Closure|string|null $concrete Concrete to bind.
	 * @param bool                 $shared Shared flag.
	 */
	public function bind( string $abstract, Closure|string|null $concrete = null, bool $shared = false ): void;

	/**
	 * Register a binding if it hasn't already been registered.
	 *
	 * @param string               $abstract Abstract name.
	 * @param \Closure|string|null $concrete Concrete to bind.
	 * @param bool                 $shared Shared flag.
	 */
	public function bind_if( string $abstract, Closure|string|null $concrete = null, bool $shared = false ): void;

	/**
	 * Register a shared binding in the container.
	 *
	 * @param string               $abstract Abstract name.
	 * @param \Closure|string|null $concrete Concrete to bind.
	 * @phpstan-param (\Closure(static, array<mixed>): mixed)|string|null $concrete
	 */
	public function singleton( string $abstract, Closure|string|null $concrete = null ): void;

	/**
	 * Register a shared binding if it hasn't already been registered.
	 *
	 * @param string               $abstract Abstract name.
	 * @param \Closure|string|null $concrete Concrete name.
	 * @phpstan-param (\Closure(static, array<mixed>): mixed)|string|null $concrete
	 */
	public function singleton_if( string $abstract, Closure|string|null $concrete = null ): void;

	/**
	 * "Extend" an abstract type in the container.
	 *
	 * @param string   $abstract Abstract name.
	 * @param \Closure $closure Closure callback.
	 */
	public function extend( string $abstract, Closure $closure ): void;

	/**
	 * Register an existing instance as shared in the container.
	 *
	 * @param string $abstract Abstract name.
	 * @param mixed  $instance Interface instance.
	 */
	public function instance( string $abstract, mixed $instance ): mixed;

	/**
	 * Get a closure to resolve the given type from the container.
	 *
	 * @param string $abstract Abstract name.
	 */
	public function factory( string $abstract ): Closure;

	/**
	 * Flush the container of all bindings and resolved instances.
	 */
	public function flush(): void;

	/**
	 * Call the given Closure / class@method and inject its dependencies.
	 *
	 * @param  array|string|callable $callback
	 * @param  array<mixed>          $parameters
	 * @param  string|null           $default_method
	 */
	public function call( $callback, array $parameters = [], $default_method = null ): mixed;

	/**
	 * Resolve the given type from the container.
	 *
	 * @param string       $abstract Abstract name.
	 * @param array<mixed> $parameters Parameters to pass.
	 */
	public function make( $abstract, array $parameters = [] ): mixed;

	/**
	 * Create a new class instance from the container.
	 *
	 * Similar to make() but specifically for passing a class name and returning
	 * an instance of it. This is useful for PHPStan.
	 *
	 * @template TAbstract of object
	 *
	 * @param  string       $class
	 * @phpstan-param class-string<TAbstract> $class
	 * @param  array<mixed> $parameters
	 * @phpstan-return TAbstract
	 */
	public function class( string $class, array $parameters = [] ): object;

	/**
	 * Determine if the given abstract type has been resolved.
	 *
	 * @param string $abstract Abstract name.
	 */
	public function resolved( string $abstract ): bool;

	/**
	 * Register a new resolving callback.
	 *
	 * @param \Closure|string $abstract Abstract name.
	 * @param \Closure|null   $callback Callback.
	 */
	public function resolving( $abstract, ?Closure $callback = null ): void;

	/**
	 * Register a new after resolving callback.
	 *
	 * @param \Closure|string $abstract Abstract name.
	 * @param \Closure|null   $callback Callback.
	 */
	public function after_resolving( $abstract, ?Closure $callback = null ): void;
}
