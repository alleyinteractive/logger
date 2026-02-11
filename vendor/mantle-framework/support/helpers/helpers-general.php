<?php
/**
 * This file contains assorted helpers
 *
 * @phpcs:disable Squiz.Commenting.FunctionComment
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 *
 * @package Mantle
 */

// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment

namespace Mantle\Support\Helpers;

use ArrayAccess;
use Carbon\Carbon as BaseCarbon;
use Countable;
use Exception;
use JsonSerializable;
use Mantle\Contracts\Support\Arrayable;
use Mantle\Contracts\Support\Jsonable;
use Mantle\Container\Container;
use Mantle\Events\Dispatcher;
use Mantle\Support\Collection;
use Mantle\Support\Enumerable;
use Mantle\Support\Higher_Order_Tap_Proxy;
use Mantle\Support\HTML;
use Mantle\Support\Str;
use Mantle\Support\Stringable;
use Mantle\Support\Uri;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Throwable;

/**
 * Determine if the given value is "blank".
 *
 * @param mixed $value Value to check.
 */
function blank( $value ): bool {
	if ( is_null( $value ) ) {
		return true;
	}

	if ( is_string( $value ) ) {
		return trim( $value ) === '';
	}

	if ( is_numeric( $value ) || is_bool( $value ) ) {
		return false;
	}

	if ( $value instanceof Countable ) {
		return count( $value ) === 0;
	}

	return empty( $value );
}

/**
 * Get the class "basename" of the given object / class.
 *
 * @param string|object $class Class or object to basename.
 */
function class_basename( string|object $class ): string {
	$class = is_object( $class ) ? $class::class : $class;

	return basename( str_replace( '\\', '/', $class ) );
}

/**
 * Returns all traits used by a class, its parent classes and trait of their traits.
 *
 * @param object|class-string $class Class or object to analyze.
 * @return array<class-string>
 */
function class_uses_recursive( string|object $class ): array {
	if ( is_object( $class ) ) {
		$class = $class::class;
	}

	$results = [];

	foreach ( array_reverse( class_parents( $class ) ?: [] ) + [ $class => $class ] as $class ) {
		$results += trait_uses_recursive( $class );
	}

	return array_unique( $results );
}

/**
 * Wrap a string in backticks.
 *
 * @param string $string The string.
 * @return string $string The wrapped string.
 */
function backtickit( string $string ): string {
	return "`{$string}`";
}

/**
 * Translate a callable into a readable string.
 *
 * Many props to Query Monitor's \QM_Util::populate_callback().
 *
 * Internals are not subject to semantic-versioning constraints.
 *
 * @param mixed $callable The plugin callback.
 * @return string The readable function name, or an empty string if untranslatable.
 */
function get_callable_fqn( mixed $callable ): string {
	$function_name = '';

	if ( \is_string( $callable ) ) {
		$function_name = $callable . '()';
	}

	if ( \is_array( $callable ) ) {
		$class  = '';
		$access = '';

		if ( \is_object( $callable[0] ) ) {
			$class  = $callable[0]::class;
			$access = '->';
		}

		if ( \is_string( $callable[0] ) ) {
			$class  = $callable[0];
			$access = '::';
		}

		if ( $class && $access ) {
			$function_name = $class . $access . $callable[1] . '()';
		}
	}

	if ( \is_object( $callable ) ) {
		$function_name = $callable::class;

		if ( ! ( $callable instanceof \Closure ) ) {
			$function_name .= '->__invoke()';
		}
	}

	return $function_name;
}

/**
 * Create a collection from the given value.
 *
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 *
 * @param iterable<TKey, TValue>|Arrayable<TKey, TValue>|Jsonable|JsonSerializable $value The value to create the collection from.
 * @return Collection<TKey, TValue>
 */
function collect( mixed $value = [] ): Collection {
	return new Collection( $value );
}

/**
 * Determine if a value is "filled".
 *
 * @param mixed $value Value to check.
 */
function filled( mixed $value ): bool {
	return ! blank( $value );
}

/**
 * Get an item from an object using "dot" notation.
 *
 * @param object      $object Object from which to get an item.
 * @param string|null $key Key path at which to get the value.
 * @param mixed       $default Default value to return on failure.
 *
 * @return mixed
 */
function object_get( $object, $key, $default = null ) {
	if ( is_null( $key ) || trim( $key ) === '' ) {
		return $object;
	}

	foreach ( explode( '.', $key ) as $segment ) {
		if ( ! is_object( $object ) || ! isset( $object->{$segment} ) ) {
			return value( $default );
		}

		$object = $object->{$segment};
	}

	return $object;
}

/**
 * Replace a given pattern with each value in the array in sequentially.
 *
 * @param string $pattern Pattern for which to search.
 * @param array<string>  $replacements Strings in which to replace sequentially.
 * @param string $subject Subject in which to search/replace.
 *
 * @return string
 */
function preg_replace_array( $pattern, array $replacements, $subject ): ?string {
	return preg_replace_callback(
		$pattern,
		function () use ( &$replacements ) {
			foreach ( $replacements as $replacement ) {
				return array_shift( $replacements );
			}
		},
		$subject
	);
}

/**
 * Retry an operation a given number of times.
 *
 * @param int           $times Number of times to retry.
 * @param callable      $callback Callable to try.
 * @param int           $sleep Number of milliseconds to sleep between tries.
 * @param callable|null $when Callable against which to check the thrown
 *                                exception to determine if a retry should not
 *                                occur.
 *
 * @return mixed
 * @throws \Exception If the callable throws an exception, it is rethrown when
 *                    the retry limit is hit or when `$when` says so.
 */
function retry( $times, callable $callback, $sleep = 0, $when = null ) {
	$attempts = 0;

	// phpcs:ignore Generic.PHP.DiscourageGoto.Found
	beginning:
	$attempts++;
	$times--;

	try {
		return $callback( $attempts );
	} catch ( Exception $e ) {
		if ( $times < 1 || ( $when && ! $when( $e ) ) ) {
			throw $e;
		}

		if ( $sleep ) {
			usleep( $sleep * 1000 );
		}

		// phpcs:ignore Generic.PHP.DiscourageGoto.Found
		goto beginning;
	}

	return null;
}

/**
 * Get a new stringable object from the given string.
 *
 * @param string $string
 */
function str( string $string = '' ): Stringable {
	return Str::of( $string );
}

/**
 * Alias to `str()`.
 *
 * @param string $string
 */
function stringable( string $string = '' ): Stringable {
	return Str::of( $string );
}

/**
 * Create a new Uri object from the given URI string.
 * If no URI is provided, it will capture the current request URI.
 *
 * @param string|null $uri The URI to create the Uri object from. Defaults to the current request URI.
 */
function uri( ?string $uri = null ): Uri {
	return $uri ? Uri::of( $uri ) : Uri::current();
}

/**
 * Call the given Closure with the given value then return the value.
 *
 * @param mixed         $value Value to provide to the callback and return.
 * @param callable|null $callback Callable to tap.
 *
 * @return mixed
 */
function tap( $value, $callback = null ) {
	if ( is_null( $callback ) ) {
		return new Higher_Order_Tap_Proxy( $value );
	}

	$callback( $value );

	return $value;
}

/**
 * Throw the given exception if the given condition is true.
 *
 * @param mixed                               $condition Condition to check.
 * @param \Throwable|class-string<\Throwable> $exception Exception to throw.
 * @param array<mixed>                               ...$parameters Params to pass to a new $exception if
 *                                         $exception is a string (classname).
 * @throws \Throwable `$exception` is thrown if `$condition` is not met.
 */
function throw_if( mixed $condition, string|Throwable $exception, ...$parameters ): mixed {
	if ( $condition ) {
		$e = is_string( $exception ) ? new $exception( ...$parameters ) : $exception;

		throw $e;
	}

	return $condition;
}

/**
 * Throw the given exception unless the given condition is true.
 *
 * @param mixed             $condition Condition to check.
 * @param \Throwable|class-string<\Throwable> $exception Exception to throw.
 * @param array<mixed>             ...$parameters Params to pass to a new $exception if
 *                                         $exception is a string (classname).
 *
 * @throws \Throwable `$exception` is thrown unless `$condition` is not met.
 */
function throw_unless( mixed $condition, string|Throwable $exception, ...$parameters ): mixed {
	if ( ! $condition ) {
		$e = is_string( $exception ) ? new $exception( ...$parameters ) : $exception;

		throw $e;
	}

	return $condition;
}

/**
 * Returns all traits used by a trait and its traits.
 *
 * @param class-string $trait Trait to check.
 *
 * @return array<string>
 */
function trait_uses_recursive( string $trait ): array|false {
	$traits = class_uses( $trait );

	foreach ( $traits as $trait ) {
		$traits += trait_uses_recursive( $trait );
	}

	return $traits;
}

/**
 * Transform the given value if it is present.
 *
 * @param mixed    $value Value to check.
 * @param callable $callback Callable to pass `$value`.
 * @param mixed    $default Fallback if `$value` is not filled. May be a
 *                           callable which accepts `$value`, or it may be any
 *                           other value which is returned directly.
 *
 * @return mixed|null
 */
function transform( mixed $value, callable $callback, $default = null ): mixed {
	if ( filled( $value ) ) {
		return $callback( $value );
	}

	if ( is_callable( $default ) ) {
		return $default( $value );
	}

	return $default;
}

/**
 * Return the given value, optionally passed through the given callback.
 *
 * @param mixed         $value Value to return.
 * @param callable|null $callback Callable to pass `$value` through.
 */
function with( mixed $value, ?callable $callback = null ): mixed {
	return is_null( $callback ) ? $value : $callback( $value );
}

/**
 * Manage the concatenation of class names based on conditions.
 *
 * A port of the classnames npm package.
 *
 * @param mixed ...$args Class names to concatenate.
 */
function classname( ...$args ): string {
	$classes = [];

	foreach ( $args as $arg ) {
		if ( is_string( $arg ) ) {
			$classes[] = $arg;
		} elseif ( is_array( $arg ) ) {
			if ( array_is_list( $arg ) ) {
				$classes[] = classname( ...$arg );
			} else {
				foreach ( $arg as $key => $value ) {
					// If the key is numeric, it's a value. Otherwise, check if it's truthy.
					if ( is_int( $key ) ) {
						$classes[] = $value;
					} elseif ( $value ) {
						$classes[] = $key;
					}
				}
			}
		} elseif ( is_object( $arg ) ) {
			$classes[] = classname( ...class_uses_recursive( $arg ) );
		} elseif ( is_int( $arg ) ) {
			$classes[] = (string) $arg;
		} elseif ( is_bool( $arg ) ) {
			$classes[] = $arg ? 'true' : 'false';
		}
	}

	return collect( $classes )->filter()->implode_str( ' ' )->trim();
}

/**
 * Display the class names based on conditions.
 *
 * @param mixed ...$args Class names to concatenate.
 */
function the_classnames( ...$args ): void {
	echo esc_attr( classname( ...$args ) );
}

/**
 * Create a new HTML instance.
 *
 * @param string $html The HTML string to test.
 */
function html_string( string $html ): HTML {
	return new HTML( $html );
}

/**
 * Capture the output of a callback.
 *
 * @param callable $callback Callback to execute.
 * @return false|string The captured output, or false on failure.
 */
function capture( callable $callback ): string|false {
	ob_start();
	$callback();
	return ob_get_clean();
}

/**
 * Add a WordPress action with type-hint support.
 *
 * @param string   $action Action to listen to.
 * @param callable $callback Callback to invoke.
 * @param int      $priority
 */
function add_action( string $hook, callable $callable, int $priority = 10 ): void {
	if ( ! class_exists( Dispatcher::class ) ) {
		\add_action( $hook, $callable, $priority, 99 );
	} else {
		Container::get_instance()->make( Dispatcher::class )->listen( $hook, $callable, $priority );
	}
}

/**
 * Add a WordPress filter with type-hint support.
 *
 * @param string   $action Action to listen to.
 * @param callable $callback Callback to invoke.
 * @param int      $priority
 */
function add_filter( string $hook, callable $callable, int $priority = 10 ): void {
	if ( ! class_exists( Dispatcher::class ) ) {
		\add_filter( $hook, $callable, $priority, 99 );
	} else {
		Container::get_instance()->make( Dispatcher::class )->listen( $hook, $callable, $priority );
	}
}

/**
 * Dispatch an event and call the listeners.
 *
 * @param  string|object  $event Event object.
 * @param  mixed          ...$payload Event payload.
 */
function event( string|object $event, mixed ...$payload ): mixed {
	return Container::get_instance()->make( 'events' )->dispatch( $event, ...$payload );
}

/**
 * Fire a callback if a hook was fired or is being fired. Otherwise, defer the
 * callback until the hook was fired.
 *
 * @param string $hook Hook to check for.
 * @param callable $callable Callable to invoke.
 * @param int $priority Hook priority.
 */
function hook_callable( string $hook, callable $callable, int $priority = 10 ): void {
	if ( ! did_action( $hook ) && ! doing_action( $hook ) ) {
		\add_action( $hook, fn () => $callable(), $priority );
	} else {
		$callable();
	}
}

/**
 * Validates a file name and path against an allowed set of rules.
 *
 * A return value of `1` means the file path contains directory traversal.
 *
 * A return value of `3` means the file is not in the allowed files list.
 *
 * @see validate_file() in WordPress core.
 *
 * @param string   $file          File path.
 * @param string[] $allowed_files Optional. Array of allowed files. Default empty array.
 * @return int 0 means nothing is wrong, greater than 0 means something was wrong.
 */
function validate_file( $file, $allowed_files = [] ) {
	// Proxy back to the core function if it exists, allowing Windows drive paths.
	if ( function_exists( 'validate_file' ) ) {
		$retval = \validate_file( $file, $allowed_files );
		return in_array( $retval, [ 0, 2 ], true ) ? 0 : $retval;
	}

	if ( ! is_scalar( $file ) || '' === $file ) { // @phpstan-ignore-line function.alreadyNarrowedType
		return 0;
	}

	// `../` on its own is not allowed:
	if ( '../' === $file ) {
		return 1;
	}

	// More than one occurrence of `../` is not allowed.
	if ( preg_match_all( '#\.\./#', $file, $matches, PREG_SET_ORDER ) && ( count( $matches ) > 1 ) ) {
		return 1;
	}

	// `../` which does not occur at the end of the path is not allowed.
	if ( str_contains( $file, '../' ) && '../' !== mb_substr( $file, -3, 3 ) ) {
		return 1;
	}

	// Files not in the allowed file list are not allowed.
	if ( ! empty( $allowed_files ) && ! in_array( $file, $allowed_files, true ) ) {
		return 3;
	}

	// Absolute Windows drive paths ARE allowed.
	return 0;
}

/**
 * Defer the execution of a function until after the response is sent to the
 * page on `shutdown`.
 *
 * @param callable $callback Callback to defer.
 * @param int      $priority Priority at which to execute the callback.
 */
function defer( callable $callback, int $priority = 10 ): void {
	static $request_sent = false;

	\add_action(
		'shutdown',
		function () use ( $callback, &$request_sent ): void {
			if ( $request_sent ) {
				if ( function_exists( 'fastcgi_finish_request' ) ) {
					fastcgi_finish_request();
				} elseif ( function_exists( 'litespeed_finish_request' ) ) {
					litespeed_finish_request();
				}

				$request_sent = true;
			}

			$callback();
		},
		$priority,
	);
}

/**
 * Dump the current backtrace to the screen.
 *
 * @param int|null $limit Limits the number of stack frames returned. By default
 *                        all stack frames are returned.
 * @param bool     $with_arguments Whether to include function arguments in
 *                                 the output.
 */
function dump_backtrace( ?int $limit = null, bool $with_arguments = false ): void {
	$frames = Backtrace::create()->offset( 1 );

	if ( null !== $limit ) {
		$frames = $frames->limit( $limit );
	}

	if ( $with_arguments ) {
		$frames = $frames->withArguments();
	}

	collect( $frames->frames() )->map( fn ( Frame $frame ) => [
		'file'        => $frame->file,
		'line number' => $frame->lineNumber,
		'class'       => $frame->class,
		'method'      => $frame->method,
		'arguments'   => $frame->arguments,
		'object'      => $frame->object,
	] )->dump();
}

/**
 * Dump the current backtrace to the screen and exit.
 *
 * @param int|null $limit Limits the number of stack frames returned. By default
 *                        all stack frames are returned.
 * @param bool     $with_arguments Whether to include function arguments in
 *                                 the output.
 */
function dd_backtrace( ?int $limit = null, bool $with_arguments = false ): never {
	dump_backtrace( $limit, $with_arguments );
	exit( 1 );
}

/**
 * Create a new Carbon instance for the current time.
 *
 * Supports time mocking during testing via Carbon::set_test_now().
 *
 * @param \DateTimeZone|string|null $tz Timezone.
 */
function now( \DateTimeZone|string|null $tz = null ): \Mantle\Support\Carbon {
	if ( ! $tz ) {
		$tz = function_exists( 'wp_timezone' ) ? wp_timezone() : new \DateTimeZone( 'UTC' );
	}

	return \Mantle\Support\Carbon::now( $tz );
}
