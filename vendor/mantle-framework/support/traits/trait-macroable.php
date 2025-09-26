<?php
/**
 * Macroable class file.
 *
 * @package Mantle
 */

// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment
// phpcs:ignoreFile: WordPressVIPMinimum.Variables.VariableAnalysis.StaticInsideClosure

namespace Mantle\Support\Traits;

use BadMethodCallException;
use Closure;
use ReflectionClass;
use ReflectionMethod;

trait Macroable {
	/**
	 * The registered string macros.
	 *
	 * @var array<mixed>
	 */
	protected static $macros = [];

	/**
	 * Register a custom macro.
	 *
	 * @param string          $name
	 * @param object|callable $macro
	 */
	public static function macro( $name, $macro ): void {
		static::$macros[ $name ] = $macro;
	}

	/**
	 * Mix another object into the class.
	 *
	 * @param object $mixin
	 * @param bool   $replace
	 *
	 *
	 * @throws \ReflectionException
	 */
	public static function mixin( $mixin, $replace = true ): void {
		$methods = ( new ReflectionClass( $mixin ) )->getMethods(
			ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
		);

		foreach ( $methods as $method ) {
			if ( $replace || ! static::has_macro( $method->name ) ) {
				static::macro( $method->name, $method->invoke( $mixin ) );
			}
		}
	}

	/**
	 * Checks if macro is registered.
	 *
	 * @param string $name
	 */
	public static function has_macro( $name ): bool {
		return isset( static::$macros[ $name ] );
	}

	/**
	 * Flush the existing macros.
	 */
	public static function flush_macros(): void {
		static::$macros = [];
	}

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @param string $method
	 * @param array<mixed>  $parameters
	 *
	 *
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic( string $method, array $parameters ): mixed {
		if ( ! static::has_macro( $method ) ) {
			throw new BadMethodCallException( sprintf(
				'Method %s::%s does not exist.', static::class, $method
			) );
		}

		$macro = static::$macros[ $method ];

		if ( $macro instanceof Closure ) {
			return call_user_func_array( Closure::bind( $macro, null, static::class ), $parameters );
		}

		return $macro( ...$parameters );
	}

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @param string $method
	 * @param array<mixed>  $parameters
	 *
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call( string $method, array $parameters ): mixed {
		if ( ! static::has_macro( $method ) ) {
			throw new BadMethodCallException( sprintf(
				'Method %s::%s does not exist.', static::class, $method
			) );
		}

		$macro = static::$macros[ $method ];

		if ( $macro instanceof Closure ) {
			return call_user_func_array( $macro->bindTo( $this, static::class ), $parameters );
		}

		return $macro( ...$parameters );
	}
}
