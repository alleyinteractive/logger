<?php
/**
 * Memoize class file
 *
 * @package Mantle
 */

declare(strict_types=1);

namespace Mantle\Support;

use Closure;
use Mantle\Support\Traits\Singleton;
use WeakMap;

/**
 * Memoize class
 *
 * This class provides a simple way to cache the results of expensive function
 * calls. Mirrors react's useMemo() by providing a callback to invoke if the
 * dependencies are changed.
 */
class Memoize {
	use Singleton;

	/**
	 * Whether memoization is enabled.
	 */
	protected static bool $enabled = true;

	/**
	 * Enable the memoization.
	 */
	public static function enable(): void {
		static::$enabled = true;
	}

	/**
	 * Disable the memoization.
	 */
	public static function disable(): void {
		static::$enabled = false;
	}

	/**
	 * Flush all memoized values.
	 */
	public static function flush(): void {
		static::instance()->values = new WeakMap();
	}

	/**
	 * Constructor.
	 *
	 * @param WeakMap<object, array<string, mixed>> $values A weak map to hold memoized values.
	 */
	protected function __construct( protected WeakMap $values = new WeakMap() ) {}

	/**
	 * Retrieve the value of a memoized callable.
	 *
	 * @param Memoizable $memoizable The memoizable instance.
	 */
	public function value( Memoizable $memoizable ): mixed {
		if ( ! static::$enabled || ! $memoizable->hash ) {
			return call_user_func( $memoizable->callable );
		}

		$object = $memoizable->object ?: $this;

		if ( ! isset( $this->values[ $object ] ) ) {
			$this->values[ $object ] = [];
		}

		if ( ! array_key_exists( $memoizable->hash, $this->values[ $object ] ) ) {
			$this->values[ $object ][ $memoizable->hash ] = call_user_func( $memoizable->callable );
		}

		return $this->values[ $object ][ $memoizable->hash ];
	}
}
