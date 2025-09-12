<?php
/**
 * Array helpers
 *
 * @package Mantle
 */

namespace Mantle\Support\Helpers;

use Mantle\Support;

/**
 * Return the default value of the given value.
 *
 * @param mixed $value Value to check.
 */
function value( mixed $value ): mixed {
	return $value instanceof \Closure ? $value() : $value;
}

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param  mixed                        $target Target to get from.
 * @param  string|array<mixed>|int|null $key Key to retrieve.
 * @param  mixed                        $default Default value.
 */
function data_get( mixed $target, string|array|int|null $key, mixed $default = null ): mixed {
	if ( is_null( $key ) ) {
		return $target;
	}

	$key = is_array( $key ) ? $key : explode( '.', (string) $key );

	foreach ( $key as $i => $segment ) {
		unset( $key[ $i ] );

		if ( is_null( $segment ) ) {
			return $target;
		}

		if ( '*' === $segment ) {
			if ( $target instanceof \Mantle\Support\Collection ) {
					$target = $target->all();
			} elseif ( ! is_array( $target ) ) {
					return value( $default );
			}

			$result = [];

			foreach ( $target as $item ) {
				$result[] = data_get( $item, $key );
			}

			return in_array( '*', $key, true ) ? Support\Arr::collapse( $result ) : $result;
		}

		if ( Support\Arr::accessible( $target ) && Support\Arr::exists( $target, $segment ) ) {
			$target = $target[ $segment ];
		} elseif ( is_object( $target ) && isset( $target->{$segment} ) ) {
			$target = $target->{$segment};
		} else {
			return value( $default );
		}
	}

	return $target;
}

/**
 * Set an item on an array or object using dot notation.
 *
 * @param  mixed               $target Array to update.
 * @param  string|array<mixed> $key Key to set.
 * @param  mixed               $value Value to set.
 * @param  bool                $overwrite Flag to overwrite the existing value.
 */
function data_set( mixed &$target, string|array $key, mixed $value, bool $overwrite = true ): mixed {
	$segments = is_array( $key ) ? $key : explode( '.', $key );
	$segment  = array_shift( $segments );

	if ( '*' === $segment ) {
		if ( ! Support\Arr::accessible( $target ) ) {
			$target = [];
		}

		if ( $segments !== [] ) {
			foreach ( $target as &$inner ) {
				data_set( $inner, $segments, $value, $overwrite );
			}
		} elseif ( $overwrite ) {
			foreach ( $target as &$inner ) {
				$inner = $value;
			}
		}
	} elseif ( Support\Arr::accessible( $target ) ) {
		if ( $segments !== [] ) {
			if ( ! Support\Arr::exists( $target, $segment ) ) {
				$target[ $segment ] = [];
			}

			data_set( $target[ $segment ], $segments, $value, $overwrite );
		} elseif ( $overwrite || ! Support\Arr::exists( $target, $segment ) ) {
			$target[ $segment ] = $value;
		}
	} elseif ( is_object( $target ) ) {
		if ( $segments !== [] ) {
			if ( ! isset( $target->{$segment} ) ) {
				$target->{$segment} = [];
			}

			data_set( $target->{$segment}, $segments, $value, $overwrite );
		} elseif ( $overwrite || ! isset( $target->{$segment} ) ) {
			$target->{$segment} = $value;
		}
	} else {
		$target = [];

		if ( $segments !== [] ) {
			data_set( $target[ $segment ], $segments, $value, $overwrite );
		} elseif ( $overwrite ) {
			$target[ $segment ] = $value;
		}
	}

	return $target;
}

/**
 * Fill in data where it's missing.
 *
 * @param mixed               $target Subject to fill into.
 * @param string|array<mixed> $key    Key(s) to fill.
 * @param mixed               $value  Value with which to fill.
 */
function data_fill( mixed &$target, string|array $key, mixed $value ): mixed {
	return data_set( $target, $key, $value, false );
}

/**
 * Get the first element of an array. Useful for method chaining.
 *
 * @param array<mixed> $array Array from which to get first element.
 */
function head( array $array ): mixed {
	return reset( $array );
}

/**
 * Get the last element from an array.
 *
 * @param array<mixed> $array Array from which to get last element.
 */
function last( array $array ): mixed {
	return end( $array );
}
