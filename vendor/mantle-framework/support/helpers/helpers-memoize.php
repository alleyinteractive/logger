<?php
/**
 * Memoize helpers.
 *
 * @package Mantle
 */

namespace Mantle\Support\Helpers;

use Closure;
use Mantle\Support\Memoize;
use Mantle\Support\Memoizable;

/**
 * Memoize a callback function.
 *
 * Caches the result of the callback based on the provided dependencies. If the
 * dependencies change, the callback will be re-evaluated. Similar to React's
 * useMemo hook.
 *
 * Useful for optimizing expensive computations that depend on specific values
 * where once() does not suffice.
 *
 * @template TReturnValue
 *
 * @param Closure           $callback The function to memoize.
 * @param array<mixed>|null $dependencies The dependencies that trigger a
 * re-evaluation.
 * @return mixed The memoized result.
 *
 * @phpstan-param Closure(): TReturnValue $callback
 * @phpstan-return TReturnValue
 */
function memo( Closure $callback, ?array $dependencies = null ): mixed {
	$memoizable = Memoizable::try_from_trace(
		debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 2 ), // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$callback,
		$dependencies,
	);

	return $memoizable instanceof Memoizable ? Memoize::instance()->value( $memoizable ) : $callback();
}
