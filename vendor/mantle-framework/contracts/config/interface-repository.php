<?php
/**
 * Repository interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Config;

use Mantle\Support\Mixed_Data;

/**
 * Config Repository Contract
 */
interface Repository {
	/**
	 * Check if a configuration value exists.
	 *
	 * @param string $key Key to get, period-delimited.
	 */
	public function has( string $key ): bool;

	/**
	 * Retrieve a configuration value.
	 *
	 * @param string $key Configuration key to get, period-delimited.
	 * @param mixed  $default Default value, optional.
	 */
	public function get( string $key, mixed $default = null ): mixed;

	/**
	 * Retrieve a configuration value as Mixed_Data.
	 *
	 * @param string $key Configuration key to get, period-delimited.
	 * @param mixed  $default Default value, optional.
	 */
	public function get_mixed( string $key, mixed $default = null ): Mixed_Data;

	/**
	 * Set a configuration value.
	 *
	 * @param array<string, mixed>|string $key Key(s) to set.
	 * @param mixed                       $value Value to set.
	 */
	public function set( array|string $key, mixed $value ): void;

	/**
	 * Get all configuration values.
	 *
	 * @return array<string, array<mixed>>
	 */
	public function all(): array;
}
