<?php
/**
 * Memoizable class file
 *
 * @package Mantle
 */

declare(strict_types=1);

namespace Mantle\Support;

use Closure;
use Laravel\SerializableClosure\Support\ReflectionClosure;

/**
 * Memoize class
 *
 * This class provides a simple way to cache the results of expensive function
 * calls. Mirrors react's useMemo() by providing a callback to invoke if the
 * dependencies are changed.
 */
class Memoizable {
	/**
	 * Retrieve a Memoizable instance.
	 *
	 * @param array<int, array<string, mixed>> $trace
	 * @param Closure                          $callable
	 * @param array<mixed>|null                $dependencies
	 */
	public static function try_from_trace( array $trace, Closure $callable, ?array $dependencies ): ?static {
		if ( $hash = static::hash_from_trace( $trace, $callable, $dependencies ) ) {
			return new static( $hash, self::object_from_trace( $trace ), $callable );
		}

		return null;
	}

	/**
	 * Retrieve a cache key from a backtrace.
	 *
	 * @param array<int, array<string, mixed>> $trace
	 * @param callable                         $callable
	 * @param array<mixed>|null                $dependencies
	 */
	protected static function hash_from_trace( array $trace, callable $callable, ?array $dependencies ): ?string {
		if ( str_contains( $trace[0]['file'] ?? '', "eval()'d code" ) ) {
			return null;
		}

		$uses = $dependencies ?? [];

		$class = $callable instanceof Closure ? ( new ReflectionClosure( $callable ) )->getClosureCalledClass()?->getName() : null;

		$class ??= $trace[1]['class'] ?? null;

		return hash( 'xxh128', sprintf(
			'%s@%s%s:%s (%s)',
			$trace[0]['file'],
			$class ? $class . '@' : '',
			$trace[1]['function'],
			$trace[0]['line'],
			serialize( $uses ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		) );
	}

	/**
	 * Computes the object of the memoizable from the given trace, if any.
	 *
	 * @param  array<int, array<string, mixed>> $trace
	 */
	protected static function object_from_trace( array $trace ): ?object {
		return $trace[1]['object'] ?? null;
	}

	/**
	 * Constructor.
	 *
	 * @param string|null $hash A unique hash representing the memoization key.
	 * @param object|null $object The object context in which the callable is executed.
	 * @param Closure     $callable The callable function to be memoized.
	 */
	public function __construct( public readonly ?string $hash, public readonly ?object $object, public readonly Closure $callable ) {}
}
