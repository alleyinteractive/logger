<?php
/**
 * Registrable interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Database;

/**
 * Registrable Model Interface
 *
 * Provides methods to register a model with WordPress either through a post type or
 * a custom taxonomy.
 */
interface Registrable {
	/**
	 * Method to register the model.
	 */
	public static function register_object(): void;

	/**
	 * Arguments to register the model with.
	 *
	 * @return array<mixed>
	 */
	public static function get_registration_args(): array;
}
